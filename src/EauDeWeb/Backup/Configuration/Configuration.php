<?php

namespace EauDeWeb\Backup\Configuration;

use Symfony\Component\Yaml\Yaml;

class Configuration {

  private static $instance = null;
  public static $config = null;

  /**
   * @return \EauDeWeb\Backup\Configuration\Configuration|null
   * @throws \Exception
   *   When cannot parse configuration file.
   */
  public static function get() {
    if (empty(self::$instance)) {
      self::$instance = new Configuration();
    }
    return self::$instance;
  }

  /**
   * Configuration constructor.
   *
   * @throws \Exception
   *   When cannot read the configuration file.
   */
  public function __construct() {
    $file = realpath(dirname(__FILE__) . '/../../../../conf/backup.config.yml');
    if (!is_readable($file)) {
      throw new \Exception("Cannot read configuration file at {$file}");
    }
    if (empty(self::$config)) {
      try {
        self::$config = Yaml::parse(file_get_contents($file));
      } catch( \Exception $e) {
        throw new \Exception("Error parsing configuration file: {$file} ({$e->getMessage()})");
      }
    }
  }


  /**
   * @return array \EauDeWeb\Backup\Configuration\MySQLServer
   */
  public function getMySQLServers() {
    $ret = [];
    if (!empty(self::$config['mysql'])) {
      foreach (self::$config['mysql'] as $id => $conf) {
        $server = MySQLServer::create(array('id' => $id) + $conf);
        $ret[$id] = $server;
      }
    }
    return $ret;
  }

  public function getRsyncTasks() {
    $ret = [];
    if (!empty(self::$config['rsync'])) {
      foreach (self::$config['rsync'] as $id => $conf) {
        $server = Rsync::create(array('id' => $id) + $conf);
        $ret[$id] = $server;
      }
    }
    return $ret;
  }

  public function getEmail() {
    if (!empty(self::$config['email'])) {
      return Email::create(self::$config['email']);
    }
    return null;
  }
}
