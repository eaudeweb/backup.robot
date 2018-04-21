<?php

namespace EauDeWeb\Backup\Configuration;


class Rsync {

  public $config;

  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * @param $config
   *
   * @return \EauDeWeb\Backup\Configuration\Rsync
   */
  public static function create($config) {
    return new Rsync($config);
  }

  public function validate() {
    // @TODO: Validate - source path is available and readable.
    // @TODO: Validate - destination server is reachable and can connect to it.
    // @TODO: Validate - destination has rsync command available
    return true;
  }

  public function config($name) {
    return !empty($this->config[$name]) ? $this->config[$name] : null;
  }

  public function from() {
    return $this->config('from');
  }

  public function to() {
    return $this->config('to');
  }

  public function user() {
    return $this->config('user');
  }

  public function host() {
    return $this->config('host');
  }
}