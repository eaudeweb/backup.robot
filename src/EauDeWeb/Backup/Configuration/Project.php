<?php

namespace EauDeWeb\Backup\Configuration;

class Project {

  public $id;
  public $config;

  public function __construct($id, $config) {
    $this->id = $id;
    $this->config = $config;
  }

  /**
   * Retrieve the configured MySQL servers to backup.
   *
   * @return array \EauDeWeb\Backup\Configuration\MySQLServer
   */
  public function getMySQLServers() {
    $ret = [];
    if (!empty($this->config['mysql'])) {
      if ($rows = $this->config['mysql']) {
        foreach ($rows as $id => $conf) {
          $server = MySQLServer::create(['id' => $id] + $conf);
          $ret[$id] = $server;
        }
      }
    }
    return $ret;
  }

  /**
   * Retrieve the configured Rsync tasks to execute.
   *
   * @return array Rsync
   */
  public function getRsyncTasks() {
    $ret = [];
    if (!empty($this->config['rsync'])) {
      if ($rows = $this->config['rsync']) {
        foreach ($rows as $id => $conf) {
          $server = Rsync::create(['id' => $id] + $conf);
          $ret[$id] = $server;
        }
      }
    }
    return $ret;
  }

}
