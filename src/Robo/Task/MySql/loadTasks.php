<?php

namespace Robo\Task\MySql;

trait loadTasks {

  /**
   * @return \Robo\Task\MySQL\Dump
   */
  protected function taskMySQLDump($database, $destination) {
    return $this->task(Dump::class, $database, $destination);
  }

  protected function taskDatabases() {
    return $this->task(Dump::class);
  }

}
