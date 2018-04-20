<?php

namespace Robo\Task\MySql;

class Dump extends Base {

  protected $destination;
  protected $database;

  public function __construct($database, $destination) {
    $this->destination = $destination;
    $this->database = $database;
  }

  public function getCommand() {
    $dest_file = $this->getDumpFilename();
    $this->option('defaults-extra-file', $this->secrets(), '=');
    $this->option('--single-transaction');
    $this->option('--quote-names');
    $this->option('--opt');
    $this->option('--no-autocommit');
    $this->option('--host', $this->host, '=');
    $this->option('--port', $this->port, '=');
    if ($this->gzip) {
      $command = 'mysqldump ' . $this->arguments . ' ' . $this->database . ' | gzip -f > ' . $dest_file;
    }
    else {
      $command = 'mysqldump ' . $this->arguments . ' ' . $this->database . ' > ' . $dest_file;
    }
    return $command;
  }

  protected function getDumpFilename() {
    $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $this->database);
    $filename = mb_ereg_replace("([\.]{2,})", '', $filename);
    $dir = rtrim($this->destination, '/');
    if ($this->gzip) {
      return $dir . '/' . $filename . '.sql.gz';
    }
    else {
      return $dir . '/' . $filename . '.sql';
    }
  }
}
