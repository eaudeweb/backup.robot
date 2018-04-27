<?php

namespace EauDeWeb\Backup\Configuration;

use EauDeWeb\Backup\BackupException;

class MySQLServer {

  public $config;

  public function __construct($config) {
    $this->config = array_merge(
      array(
        'user' => 'root',
        'host' => '127.0.0.1',
        'port' => 3306,
        'gzip' => true
      ),
      $config
    );
  }

  public function validate() {
    // @TODO: Validate - mysql command line exists
    // @TODO: Validate - gzip is available if gzip flag is set.
    return $this->validateMysqliExtension() && $this->validateBackupWritable() && $this->validateConnection();
  }

  public function validateMysqliExtension() {
    return extension_loaded('mysqli');
  }

  public function validateBackupWritable() {
    return is_writable($this->backupDestination());
  }

  public function validateConnection() {
    $ret = false;
    if ($this->validateMysqliExtension()) {
      if ($conn = $this->connect()) {
        @mysqli_close($conn);
      }
      $ret = !empty($conn);
    }
    return $ret;
  }

  public function connect() {
    $conn = @mysqli_connect($this->host(), $this->user(), $this->password(), NULL, $this->port());
    if (@mysqli_connect_errno()) {
      \Robo\Robo::logger()->critical(@mysqli_connect_error());
    }
    return $conn;
  }

  /**
   * @param $config
   *
   * @return \EauDeWeb\Backup\Configuration\MySQLServer
   */
  public static function create($config) {
    return new MySQLServer($config);
  }

  public function id() {
    return $this->config('id');
  }

  public function host() {
    return $this->config('host');
  }

  public function user() {
    return $this->config('user');
  }

  public function password() {
    return $this->config('password');
  }

  public function port() {
    return $this->config('port');
  }

  public function gzip() {
    return $this->config('gzip');
  }

  /**
   * @todo Not implemented.s
   * @return null
   */
  public function socket() {
    return $this->config('socket');
  }

  public function blacklist() {
    return $this->config('blacklist');
  }

  public function backupDestination() {
    return $this->config('destination');
  }

  public function config($name) {
    return isset($this->config[$name]) ? $this->config[$name] : null;
  }

  /**
   * Get a list of databases from the server.
   */
  public function databases() {
    $ret = [];
    if ($conn = $this->connect()) {
      if ($result = @mysqli_query($conn, 'SHOW DATABASES')) {
        while ($row = $result->fetch_row()) {
          $ret [] = $row[0];
        }
      }
    }
    return $ret;
  }

  public function databasesToBackup() {
    $ret = array_filter($this->databases(), function($value) {
      return !in_array($value, $this->blacklist());
    });
    return $ret;
  }

  /**
   * Make backups preparations (i.e. create backup directories if they do not exist
   *
   * @throws \EauDeWeb\Backup\BackupException
   *   When preparation fails.
   */
  public function prepare() {
    $destination = $this->backupDestination();
    if (!is_writable($destination)) {
      throw new BackupException("Dump destination missing, please create path: {$destination}");
    }
  }
}
