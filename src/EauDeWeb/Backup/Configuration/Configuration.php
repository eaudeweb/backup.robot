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
    if ($rows = \Robo\Robo::config()->get('backup.projects')) {
      foreach ($rows as $id => $config) {
        $ret[$id] = new Project($id, $config);
      }
    }
    return $ret;
  }

  public function getDefaultEmail() {
    $ret = null;
    if (\Robo\Robo::config()
        ->get('defaults.email') == TRUE
        && $config = \Robo\Robo::config()->get('defaults.email')) {
      $ret = Email::create($config);
    }
    return $ret;
  }

}
