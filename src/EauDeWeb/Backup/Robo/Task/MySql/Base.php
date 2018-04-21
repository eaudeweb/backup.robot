<?php

namespace EauDeWeb\Backup\Robo\Task\MySql;


use Robo\Common\ExecOneCommand;
use Robo\Task\BaseTask;

abstract class Base extends BaseTask {

  use ExecOneCommand;

  protected $host;
  protected $port;
  protected $user;
  protected $password;
  protected $gzip;
  protected $socket;
  protected $tmp = '/tmp';
  protected $secret;

  abstract function getCommand();

  public function run() {
    try {
      $command = $this->getCommand();
      $this->executeCommand($command);
    } catch (\Exception $e) {
      //@TODO
    }
    finally {
      $this->cleanup();
    }
  }

  public function host($host) {
    $this->host = $host;
    return $this;
  }

  public function port($port) {
    $this->port = $port;
    return $this;
  }

  public function user($user) {
    $this->user = $user;
    return $this;
  }

  public function password($password) {
    $this->password = $password;
    return $this;
  }

  public function gzip($gzip = TRUE) {
    $this->gzip = $gzip;
    return $this;
  }

  public function socket($socket) {
    $this->socket = $socket;
    return $this;
  }

  public function secrets() {
    if (empty($this->secret)) {
      $this->secret = $this->tmp . '/bk-robot-' . uniqid();
      file_put_contents(
        $this->secret,
        sprintf("[client]\nuser=%s\npassword=%s", $this->user, $this->password)
      );
    }
    return $this->secret;
  }

  public function cleanup() {
    if (unlink($this->secret)) {
      $this->secret = NULL;
    }
    else {
      // TODO
      return false;
    }
  }
}
