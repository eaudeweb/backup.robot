<?php
/**
 * If we're running from phar load the phar autoload file.
 */
$pharPath = \Phar::running(true);
if ($pharPath) {
  require_once "$pharPath/vendor/autoload.php";
} else {
  if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
  }
  elseif (file_exists(__DIR__ . '/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
  }
}
$output = new \Symfony\Component\Console\Output\NullOutput();
#$output = new \Symfony\Component\Console\Output\BufferedOutput();
#$output = new \Symfony\Component\Console\Output\ConsoleOutput();
$statusCode = \Robo\Robo::run(
  $_SERVER['argv'],
  \EauDeWeb\Backup\Commands\RoboFile::class,
  'BackupRobot',
  '0.0.2',
  $output
);
exit($statusCode);
