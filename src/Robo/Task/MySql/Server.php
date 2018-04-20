<?php

namespace Robo\Task\MySql;


use Robo\Contract\CommandInterface;
use Robo\Task\BaseTask;

abstract class Server extends BaseTask implements CommandInterface {

  /**
   * Returns command that can be executed.
   * This method is used to pass generated command from one task to another.
   *
   * @return string
   */
  public function getCommand() {
    // TODO: Implement getCommand() method.
  }

  /**
   * @return \Robo\Result
   */
  public function run() {
    // TODO: Implement run() method.
  }
}