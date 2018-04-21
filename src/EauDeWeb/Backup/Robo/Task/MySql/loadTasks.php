<?php

namespace EauDeWeb\Backup\Robo\Task\MySql;

trait loadTasks {

  /**
   * @return \EauDeWeb\Backup\Robo\Task\MySql\Dump
   */
  protected function taskMySQLDump($database, $destination) {
    return $this->task(Dump::class, $database, $destination);
  }

  protected function taskDatabases() {
    return $this->task(Dump::class);
  }

}
