<?php

namespace EauDeWeb\Backup\Configuration;

/**
 * Class Configuration is used to access configuration for the backup process.
 *
 * @package EauDeWeb\Backup\Configuration
 */
class Configuration {

  private static $instance = null;

  /**
   * @return \EauDeWeb\Backup\Configuration\Configuration|null
   */
  public static function get() {
    if (empty(self::$instance)) {
      self::$instance = new Configuration();
    }
    return self::$instance;
  }

  public function getProjects() {
    $ret = [];
    if ($projects = \Robo\Robo::config()->get('backup.projects')) {
      foreach ($projects as $name => $config) {
        var_dump($config);
      }

    }
    return $ret;
  }

  /**
   * Retrieve the configured MySQL servers to backup.
   *
   * @return array \EauDeWeb\Backup\Configuration\MySQLServer
   */
  public function getMySQLServers() {
    $ret = [];
    if ($servers = \Robo\Robo::config()->get('backup.mysql')) {
      foreach ($servers as $id => $conf) {
        $server = MySQLServer::create(array('id' => $id) + $conf);
        $ret[$id] = $server;
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
    if ($tasks = \Robo\Robo::config()->get('backup.rsync')) {
      foreach ($tasks as $id => $conf) {
        $server = Rsync::create(array('id' => $id) + $conf);
        $ret[$id] = $server;
      }
    }
    return $ret;
  }

  public function getEmail() {
    $ret = null;
    if ($config = \Robo\Robo::config()->get('backup.email')) {
      $ret = Email::create($config);
    }
    return $ret;
  }
}
