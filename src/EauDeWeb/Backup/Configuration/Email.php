<?php
/**
 * Created by PhpStorm.
 * User: cristiroma
 * Date: 4/20/18
 * Time: 7:58 PM
 */

namespace EauDeWeb\Backup\Configuration;


class Email {

  public $config;

  public function __construct($config) {
    $this->config = $config;
  }

  /**
   * @param $config
   *
   * @return \EauDeWeb\Backup\Configuration\Email
   */
  public static function create($config) {
    return new Email($config);
  }
}