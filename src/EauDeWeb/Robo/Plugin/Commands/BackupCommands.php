<?php

namespace EauDeWeb\Robo\Plugin\Commands;

use EauDeWeb\Backup\Configuration\Configuration;

class BackupCommands extends \Robo\Tasks implements BackupInterface {

  use \Robo\Task\MySql\loadTasks;

  const VERSION = "0.0.1";

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
        $this->say(sprintf("        - Backup to %s", $server->backupDir()));
        if ($server->validateBackupWritable()) {
          $this->say(sprintf("        - Backup writable: YES"));
        }
        else {
          $this->yell(sprintf("       - Backup writable: NO"), NULL, 'red');
        }
      }
  }

  /**
   * Prepare backups (i.e. MySQL dumps, archives etc.)
   * @command backup:prepare
   * @throws \Exception
   *   When something went wrong.
   */
  public function prepare() {
    $dbServers = Configuration::get()->getMySQLServers();
    /** @var \EauDeWeb\Backup\Configuration\MySQLServer $server */
    foreach ($dbServers as $k => $server) {
      $this->say("[MySQL] Running prepare for: {$k}");
      $server->prepare();
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
    $this->say("Registering shutdown hook");
    register_shutdown_function([self::class, 'shutdown']);
    if (function_exists('pcntl_signal')) {
      pcntl_signal(SIGINT, [self::class, 'shutdown']);
    }

    $dbServers = Configuration::get()->getMySQLServers();
    /** @var \EauDeWeb\Backup\Configuration\MySQLServer $server */
    foreach ($dbServers as $k => $server) {
      $this->say("[MySQL][{$k}] Starting backup process for {$server->user()}@{$server->host()}");
      $this->say("[MySQL][{$k}] Preparing backups");
      $databases = $server->databasesToBackup();
      if (empty($databases)) {
        $this->yell("[MySQL][{$k}] Warning, no databases selected for backup!", NULL, 'yellow');
      }
      foreach ($databases as $database) {
        try {
          $server->prepare();
          $this->say("[MySQL][{$k}][{$database}] Dumping database");
          $this->taskMySQLDump($database, $server->backupDestination())
            ->host($server->host())
            ->port($server->port())
            ->user($server->user())
            ->password($server->password())
            ->gzip($server->gzip())
            ->socket($server->socket())
            ->run();
//          $status = $server->backup();
//          switch ($status) {
//            case self::BACKUP_SUCCESS:
//              $this->say("[MySQL][{$k}] Finished successfully");
//              break;
//            case self::BACKUP_PARTIAL:
//              $this->yell("[MySQL][{$k}] Partial backup", NULL, 'yellow');
//              break;
//            case self::BACKUP_ERROR:
//              $this->yell("[MySQL][{$k}] Failed", NULL, 'red');
//          }
        } catch (\Exception $e) {
          $this->yell("[MySQL] Backup failed for: {$k} ({$e->getMessage()})", NULL, 'red');
        }
      }
    }
  }
}
