<?php

namespace EauDeWeb\Backup\Commands;

use EauDeWeb\Backup\Configuration\BackupLogger;
use EauDeWeb\Backup\Configuration\Configuration;
use Robo\Robo;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

  use \EauDeWeb\Backup\Robo\Task\MySql\loadTasks;

  public function __construct() {
    $tz = Robo::config()->get('backup.defaults.timezone');
    date_default_timezone_set($tz);
  }

  /**
   * Health check and information about the backup status.
   *
   * @command backup:status
   * @throws \Exception
   *   When trying to initialize the backup framework
   */
  public function status() {
    $app = Robo::application();
    $this->say(sprintf("🤖 %s v.%s - Crafted with ♥ at www.eaudeweb.ro", $app->getName(), $app->getVersion()));
    $this->say("############### Configuration summary ###############");
    $config = Configuration::create(Robo::config()->get('backup'));
    $projects = $config->getProjects();
    /** @var \EauDeWeb\Backup\Configuration\Project $project */
    foreach($projects as $id => $project) {
      $this->say("    Project: ${id}");
      $dbServers = $project->getMySQLServers();
      $this->say("        MySQL databases:");
      /** @var  \EauDeWeb\Backup\Configuration\MySQLServer $server */
      foreach ($dbServers as $k => $server) {
        $this->say(sprintf("            - {$k} (%s@%s)", $server->user(), $server->host()));
        if ($server->validateConnection()) {
          $this->say(sprintf("            - Connected successfully (mysqli loaded)"));
        }
        else {
          $this->yell(sprintf("            - Cannot connect"), NULL, 'red');
        }
        $this->say(sprintf("            - Backup to %s", $server->backupDestination()));
        if ($server->validateBackupWritable()) {
          $this->say(sprintf("            - Backup writable: YES"));
        }
        else {
          $this->yell(sprintf("            - Backup writable: NO"), NULL, 'red');
        }
        $databases = $server->databasesToBackup();
        $this->say(sprintf("            - Backup databases: [%s]", implode(', ', $databases)));
      }

      // Rsync tasks
      $rsyncTasks = $project->getRsyncTasks();
      if (!empty($rsyncTasks)) {
        $this->say("        Rsync tasks:");
        /** @var \EauDeWeb\Backup\Configuration\Rsync $task */
        foreach ($rsyncTasks as $task) {
          if ($task->validateLocalRsync($this->taskExec('which rsync'))) {
            $this->say(sprintf("            - This host has rsync installed"));
          }
          else {
            $this->yell(sprintf("            - This host is missing rsync command"), NULL, 'red');
          }
          if ($task->validateConnection($this->taskSshExec($task->host(), $task->user()))) {
            $this->say(sprintf("            - Connected successfully (%s@%s:%d), target has rsync", $task->user(), $task->host(), $task->port()));
          }
          else {
            $this->yell(sprintf("       - Unsuitable server: (%s@%s:%d)  - host unreachable or does not have rsync installed)", $task->user(), $task->host(), $task->port()), NULL, 'red');
          }
        }
      }
    }
  }

  /**
   * Create the actual backup based on the configuration.
   *
   * @command backup:backup
   * @throws \Exception
   *   When the configuration fails.
   */
  public function backup() {
    $status = true;
    $log = BackupLogger::get();
    $app = Robo::application();
    $this->say(sprintf("🤖 %s v.%s - Crafted with ♥ at www.eaudeweb.ro", $app->getName(), $app->getVersion()));
    $log->debug("Registering shutdown hook");
    register_shutdown_function([self::class, 'shutdown']);
    if (function_exists('pcntl_signal')) {
      pcntl_signal(SIGINT, [self::class, 'shutdown']);
    }
    $config = Configuration::create(Robo::config()->get('backup'));
    $projects = $config->getProjects();

    /** @var \EauDeWeb\Backup\Configuration\Project $project */
    foreach($projects as $id => $project) {
      $log->info("Processing project: {$id}");
      // MySQL-related tasks
      $dbServers = $project->getMySQLServers();
      /** @var \EauDeWeb\Backup\Configuration\MySQLServer $server */
      foreach ($dbServers as $k => $server) {
        $log->info("[$id][MySQL][{$k}] Starting backup process for {$server->user()}@{$server->host()}");
        if (!$server->validate()) {
          $log->error("[$id][MySQL][{$k}] Server validation failed, aborting ...");
          $status = false;
          continue;
        }
        $databases = $server->databasesToBackup();
        if (empty($databases)) {
          $status = false;
          $log->warning("[$id][MySQL][{$k}] Warning, no databases selected for backup!");
        }
        $server->prepare();
        foreach ($databases as $database) {
          try {
            /** @var \Robo\Collection\CollectionBuilder $mysql */
            $mysql = $this->taskMySQLDump($database, $server->backupDestination())->interactive(false);
            /** @var \Robo\Result $result */
            $result = $mysql->host($server->host())
              ->port($server->port())
              ->user($server->user())
              ->password($server->password())
              ->gzip($server->gzip())
              ->socket($server->socket())
              ->run();
            $log->info("[$id][MySQL][{$k}][{$database}] {$mysql->getCommand()}");
            $output = $result->getMessage();
            if (!empty(trim($output))) {
              $log->info("[$id][MySQL][{$k}][{$database}] {$output}");
            }
            $success = $result->wasSuccessful();
            $status = $status && $success;
            $log->debug(sprintf("[$id][MySQL][{$k}][{$database}] Execution took: %.1f seconds", $result->getExecutionTime()));
            $log->info(sprintf("[$id][MySQL][{$k}][{$database}] Result: %s", $success ? 'Success' : 'Failure'));
          } catch (\Exception $e) {
            $log->error("[MySQL] Backup failed for: {$k} ({$e->getMessage()})");
          }
        }
      }

      // Rsync tasks
      $rsyncTasks = $project->getRsyncTasks();
      /** @var \EauDeWeb\Backup\Configuration\Rsync $task */
      foreach ($rsyncTasks as $k => $task) {
        $log->debug("[$id][Rsync][{$k}] Source      : {$task->from()}");
        $log->debug("[$id][Rsync][{$k}] Destination : {$task->user()}@{$task->host()}:{$task->to()}");
        $rsync = $this->taskRsync()->interactive(false)->printOutput(false);
        if ($task->fakeSuper()) {
          $rsync->option('--rsync-path', 'rsync --fake-super', '=');
        }
        $result = $rsync->fromPath($task->from())
          ->option('-e', 'ssh -p ' . $task->port())
          ->option('--no-specials')
          ->option('--no-devices')
          ->toHost($task->host())
          ->toUser($task->user())
          ->toPath($task->to())
          #->checksum()
          #->wholeFile()
          ->recursive()
          ->delete()
          ->verbose()
          ->progress()
          ->humanReadable()
          ->stats()
          ->excludeVcs()
          ->run();
        $log->info("[$id][Rsync][{$k}] {$rsync->getCommand()}");
        if ($output = $result->getMessage()) {
          $log->info("[$id][Rsync][{$k}] {$output}");
        }
        $success = $result->wasSuccessful();
        $status = $status && $success;
        $log->info(sprintf("[$id][Rsync][{$k}] Execution took: %.1f seconds", $result->getExecutionTime()));
        $log->info("[$id][Rsync][{$k}] Result: " . ($result->wasSuccessful() ? 'Success' : 'Failure'));
      }
    }

    if ($email = $config->getDefaultEmail()) {
      $subject = $status === true ? $email->subjectSuccess() : $email->subjectFail();
      $message = $email->createEmailBackupReport($subject, $log->getLogFilePath(), 'Output log');
      $message->send();
    }
  }

  /**
   * It does nothing, used for development and debugging.
   * @command backup:dummy
   * @throws \Exception
   *   When something went wrong.
   */
  public function dummy() {
    $config = Configuration::create(Robo::config()->get('backup'));

    /** @var \EauDeWeb\Backup\Configuration\Email $email */
    $email = $config->getDefaultEmail();
    $message = $email->createEmailBackupReport('TEST', '/var/log/aptitude', 'This is a default body');
    $message->send();
  }

  /**
   * Intercept system closing and cleanup remaining tempoary files.
   */
  public static function shutdown() {
    $tmp = '/tmp';
    if ($handle = opendir($tmp)) {
      while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
          // @todo better handling of temporary file cleanup.
          if (strpos($entry, 'bk-robot') !== FALSE) {
            unlink($tmp . '/' . $entry);
          }
        }
      }
      closedir($handle);
    }
  }
}
