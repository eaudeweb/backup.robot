<?php

use EauDeWeb\Backup\Configuration\Configuration;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

  use EauDeWeb\Backup\Robo\Task\MySql\loadTasks;

  /** This backup.robot release version */
  const VERSION = "0.0.1";

  /**
   * Health check and information about the backup status.
   *
   * @command backup:status
   * @throws \Exception
   *   When trying to initialize the backup framework
   */
  public function status() {
    $this->say(sprintf("Backup rObOt v.%s - Crafted with ♥ at www.eaudeweb.ro", self::VERSION));
    $this->say("========== Backup summary ==========");
    $config = Configuration::get();
    $dbServers = $config->getMySQLServers();
    $this->say("MySQL databases:");
    /** @var  \EauDeWeb\Backup\Configuration\MySQLServer $server */
    foreach ($dbServers as $k => $server) {
      $this->say(sprintf("    - {$k} (%s@%s)", $server->user(), $server->host()));
      if ($server->validateConnection()) {
        $this->say(sprintf("        - Connected successfully (mysqli loaded)"));
      }
      else {
        $this->yell(sprintf("       - Cannot connect"), NULL, 'red');
      }
      $this->say(sprintf("        - Backup to %s", $server->backupDestination()));
      if ($server->validateBackupWritable()) {
        $this->say(sprintf("        - Backup writable: YES"));
      }
      else {
        $this->yell(sprintf("       - Backup writable: NO"), NULL, 'red');
      }
      $databases = $server->databasesToBackup();
      $this->say(sprintf("        - Backup databases: [%s]", implode(', ', $databases)));
    }

    // Rsync tasks
    $rsyncTasks = Configuration::get()->getRsyncTasks();
    if (!empty($rsyncTasks)) {
      $this->say("Rsync tasks:");
      /** @var \EauDeWeb\Backup\Configuration\Rsync $task */
      foreach ($rsyncTasks as $task) {
        if ($task->validateLocalRsync($this->taskExec('which rsync'))) {
          $this->say(sprintf("        - This host has rsync installed"));
        }
        else {
          $this->yell(sprintf("       - This host is missing rsync command"), NULL, 'red');
        }
        if ($task->validateConnection($this->taskSshExec($task->host(), $task->user()))) {
          $this->say(sprintf("        - Connected successfully (%s@%s:%d), target has rsync", $task->user(), $task->host(), $task->port()));
        }
        else {
          $this->yell(sprintf("       - Unsuitable server: (%s@%s:%d)  - host unreachable or does not have rsync installed)", $task->user(), $task->host(), $task->port()), NULL, 'red');
        }
      }
    }
  }

  /**
   * Prepare backups (i.e. MySQL dump destination folders, archives etc.)
   * @command backup:prepare
   * @throws \Exception
   *   When something went wrong.
   */
  public function prepare() {
    $dbServers = Configuration::get()->getMySQLServers();
    /** @var \EauDeWeb\Backup\Configuration\MySQLServer $server */
    foreach ($dbServers as $k => $server) {
      #$this->say("[MySQL] Running prepare for: {$k}");
      $server->prepare();
    }
    $this->say("Preparation done.");
  }

  /**
   * Create the actual backup based on the configuration.
   *
   * @command backup:backup
   * @throws \Exception
   *   When the configuration fails.
   */
  public function backup() {
    $this->say("Registering shutdown hook");
    register_shutdown_function([self::class, 'shutdown']);
    if (function_exists('pcntl_signal')) {
      pcntl_signal(SIGINT, [self::class, 'shutdown']);
    }

    // MySQL-related tasks
    $dbServers = Configuration::get()->getMySQLServers();
    /** @var \EauDeWeb\Backup\Configuration\MySQLServer $server */
    foreach ($dbServers as $k => $server) {
      $this->say("[MySQL][{$k}] Starting backup process for {$server->user()}@{$server->host()}");
      $this->say("[MySQL][{$k}] Preparing backups");
      $databases = $server->databasesToBackup();
      if (empty($databases)) {
        $this->yell("[MySQL][{$k}] Warning, no databases selected for backup!", NULL, 'yellow');
      }
      $server->prepare();
      foreach ($databases as $database) {
        try {
          $this->say("[MySQL][{$k}][{$database}] Dumping database");
          $this->taskMySQLDump($database, $server->backupDestination())
            ->host($server->host())
            ->port($server->port())
            ->user($server->user())
            ->password($server->password())
            ->gzip($server->gzip())
            ->socket($server->socket())
            ->run();
        } catch (\Exception $e) {
          $this->yell("[MySQL] Backup failed for: {$k} ({$e->getMessage()})", NULL, 'red');
        }
      }
    }

    // Rsync tasks
    $rsyncTasks = Configuration::get()->getRsyncTasks();
    /** @var \EauDeWeb\Backup\Configuration\Rsync $task */
    foreach ($rsyncTasks as $k => $task) {
      $this->say("[Rsync][{$k}] Starting rsync process for {$task->user()}@{$task->host()}");
      $this->say("[Rsync][{$k}]     Source: {$task->from()}");
      $this->say("[Rsync][{$k}]     Destination: {$task->user()}@{$task->host()}:{$task->to()}");
      $this->taskRsync()
        ->fromPath($task->from())
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
        ->option('--rsync-path', 'rsync --fake-super', '=')
        ->run();
    }
  }

  /**
   * It does nothing, used for development and debugging.
   * @command backup:dummy
   * @throws \Exception
   *   When something went wrong.
   */
  public function dummy() {
    /** @var \EauDeWeb\Backup\Configuration\Email $email */
    $email = Configuration::get()->getEmail();
    $message = $email->createMessage('TEST', 'test1122');
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
