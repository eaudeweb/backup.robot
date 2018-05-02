<?php

namespace EauDeWeb\Backup\Configuration;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Class BackupLogger
 *
 * @package EauDeWeb\Backup\Configuration
 * @codeCoverageIgnore
 */
class BackupLogger extends AbstractLogger {

  private static $instance = null;
  private $log = null;
  private $logFilePath = null;

  private function __construct(LoggerInterface $log, $logFilePath = NULL) {
    $this->log = $log;
    $this->logFilePath = $logFilePath;
  }

  public function getLogFilePath() {
    return $this->logFilePath;
  }

  /**
   * @return \EauDeWeb\Backup\Configuration\BackupLogger
   */
  public static function fileLogger() {
    if (empty(self::$instance)) {
      $log = new Logger('backup');
      $path = realpath(__DIR__ . '/../../../../logs/');
      $logFilePath = sprintf('%s/backup.%s.log', $path, date('Ymd'));
      $file = new StreamHandler($logFilePath, Logger::DEBUG);
      $file->setFormatter(new LineFormatter("[%datetime%] %level_name%: %message%\n", 'H:i:s', true));
      $log->pushHandler($file);
      self::$instance = new BackupLogger($log, $logFilePath);
    }
    return self::$instance;
  }

  /**
   * @return \EauDeWeb\Backup\Configuration\BackupLogger
   */
  public static function statusLogger() {
    if (empty(self::$instance)) {
      $log = new Logger('backup');
      $file = new StreamHandler('php://stdout', Logger::DEBUG);
      $file->setFormatter(new ColoredLineFormatter(null,"[%datetime%] %message%\n", 'H:i:s', true));
      $log->pushHandler($file);
      self::$instance = new BackupLogger($log);
    }
    return self::$instance;
  }

  public function log($level, $message, array $context = array()) {
    $this->log->$level($message, $context);
  }
}
