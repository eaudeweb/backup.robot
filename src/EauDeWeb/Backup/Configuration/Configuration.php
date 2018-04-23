<?php

namespace EauDeWeb\Backup\Configuration;

/**
 * Class Configuration is used to access configuration for the backup process.
 *
 * @package EauDeWeb\Backup\Configuration
 */
class Configuration {

  private static $instance = null;
  protected static $config;

  public function __construct($array) {
    self::$config = $array;
  }

  /**
   * @return \EauDeWeb\Backup\Configuration\Configuration|null
   */
  public static function create(array $array) {
    self::$instance = new Configuration($array);
    return self::$instance;
  }

  /**
   * @return \EauDeWeb\Backup\Configuration\Configuration
   */
  public static function get() {
    return self::$instance;
  }

  public function getProjects() {
    $ret = [];
    if (!empty(self::$config['projects'])
        && $rows = self::$config['projects']) {
      foreach ($rows as $id => $config) {
        $ret[$id] = new Project($id, $config);
      }
    }
    return $ret;
  }

  public function getDefaultEmail() {
    $ret = null;
    if (!empty(self::$config['defaults']['email'])
        && $config = self::$config['defaults']['email']) {
      $ret = Email::create($config);
    }
    return $ret;
  }
}
