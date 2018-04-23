<?php

namespace EauDeWeb\Backup\Configuration;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\AbstractLogger;

/**
 * Class BackupLogger
 *
 * @package EauDeWeb\Backup\Configuration
 * @codeCoverageIgnore
 */
class BackupLogger extends AbstractLogger {

  private static $instance = null;
  private static $fileLogger = null;
  private static $logFile = null;

  private function __construct() {
    self::$fileLogger = new Logger('backup');
    $path = realpath(__DIR__ . '/../../../../logs/');
    self::$logFile = sprintf('%s/backup.%s.log', $path, date('Ymd'));
    self::$fileLogger->pushHandler(new StreamHandler(self::$logFile, Logger::DEBUG));
  }

  public function getLogFilePath() {
    return self::$logFile;
  }

  /**
   * @return \EauDeWeb\Backup\Configuration\BackupLogger
   */
  public static function get() {
    if (empty(self::$instance)) {
      self::$instance = new BackupLogger();
    }
    return self::$instance;
  }

  /**
   * @return \Psr\Log\LoggerInterface
   */
  protected function getActualLogger() {
    return \Robo\Robo::logger();
  }

  protected function getFileLogger() {
    return self::$fileLogger;
  }

  protected function doLog($level, $message, array $context = array()) {
    $method = $level;
    $this->getActualLogger()->$method($message);
    $this->getFileLogger()->$method($message);
  }

  public function log($level, $message, array $context = array()) {
    $this->doLog($level, $message, $context);
  }
}
