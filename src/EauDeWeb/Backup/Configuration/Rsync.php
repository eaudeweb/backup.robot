<?php

namespace EauDeWeb\Backup\Configuration;


class Rsync {

  public $config;

  public function __construct($config) {
    $this->config = array_merge(
      array(
        'port' => 22,
        'fake-super' => false
      ),
      $config
    );
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
    // @TODO: Validate local rsync
    return true;
  }

  /**
   * @param \Robo\Task\Base\Exec $runner
   *
   * @return bool
   */
  public function validateLocalRsync($runner) {
    /** @var \Robo\Result $result */
    $runner->silent(true);
    $result = $runner->run();
    if ($result->getExitCode() === 0) {
      return true;
    }
    return false;
  }

  /**
   * @param \Robo\Task\Remote\Ssh $sshTask
   *
   * @return bool
   */
  public function validateConnection($runner) {
    $runner->silent(true);
    /** @var \Robo\Result $result */
    $result = $runner->port($this->port())->exec('which rsync')->run();
    if ($result->getExitCode() === 0) {
      return true;
    }
    return false;
  }

  public function config($name) {
    return isset($this->config[$name]) ? $this->config[$name] : null;
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

  public function port() {
    return $this->config('port');
  }

  public function fakeSuper() {
    return $this->config('fake-super');
  }
}