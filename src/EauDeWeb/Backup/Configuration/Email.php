<?php
/**
 * Created by PhpStorm.
 * User: cristiroma
 * Date: 4/20/18
 * Time: 7:58 PM
 */

namespace EauDeWeb\Backup\Configuration;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {

  const EMAIL_SUCCESS = 1;
  const EMAIL_FAIL = 0;

  public $config;
  protected static $attachment_treshold_default = 256000;

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

  /**
   * Is email sending enabled?
   *
   * @return bool
   */
  public function isEnabled() {
    return !empty($this->config['enabled']) ? $this->config['enabled'] : false;
  }

  /**
   * @param string $subject
   *   Message subject
   * @param string $body
   * @param string $bodyAlt
   *
   * @return null|\PHPMailer\PHPMailer\PHPMailer
   */
  public function createMessage($subject, $body = '', $bodyAlt = '') {
    $mail = new PHPMailer(true); // Passing `true` enables exceptions
    try {
      //Server settings
      $mail->SMTPDebug = $this->serverDebugLevel();
      if ($this->serverType() == 'smtp') {
        $mail->isSMTP();
      }
      else if ($this->serverType() == 'mail') {
        $mail->isMail();
      }
      else if ($this->serverType() == 'sendmail') {
        $mail->isSendmail();
      }
      else if ($this->serverType() == 'qmail') {
        $mail->isQmail();
      }
      $mail->Host = $this->serverHost();
      $mail->Port = $this->serverPort();
      $mail->SMTPAuth = $this->serverAuthenticate();
      $mail->Username = $this->serverUsername();
      $mail->Password = $this->serverPassword();
      $mail->SMTPSecure = $this->serverProtocol();

      //Recipients
      $mail->setFrom($this->from());
      $mail->addAddress($this->to());
      # $mail->addAddress($this->to());
      # $mail->addReplyTo('info@example.com', 'Information');
      # $mail->addCC('cc@example.com');
      # $mail->addBCC('bcc@example.com');

      //Attachments
      # $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
      # $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

      //Content
      # $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = $subject;
      $mail->Body    = $body;
      $mail->AltBody = $bodyAlt;
      return $mail;
    } catch (Exception $e) {
      // @TODO
    }
    return null;
  }

  public function createEmailBackupReport($subject, $logPath, $body = '', $bodyAlt = '') {
    $message = $this->createMessage($subject, $body, $bodyAlt);
    if (is_readable($logPath) && $size = @filesize($logPath)) {
      if ($size <= $this->attachmentThreshold()) {
        $message->Body = $body . "\n\n ========== Backup log ==========\n\n" . file_get_contents($logPath);
      }
      else {
        if ($this->attachmentCompress()) {
          // @TODO
        }
        else {
          try {
            $message->addAttachment($logPath);
          } catch (\Exception $e) {
            // @TODO
          }
        }
      }
    }

    return $message;
  }

  public function config($name) {
    return !empty($this->config[$name]) ? $this->config[$name] : null;
  }

  public function from() {
    return $this->config('from');
  }

  public function to() {
    return $this->config('to');
  }

  public function subjectSuccess() {
    $arr = $this->config('subject');
    return !empty($arr['success']) ? $arr['success'] : null;
  }

  public function subjectFail() {
    $arr = $this->config('fail');
    return !empty($arr['fail']) ? $arr['fail'] : null;
  }

  public function server() {
    return $this->config('server');
  }

  public function serverDebugLevel() {
    $arr = $this->config('server');
    return !empty($arr['debug-level']) ? $arr['debug-level'] : null;
  }

  public function serverType() {
    $arr = $this->config('server');
    return !empty($arr['type']) ? $arr['type'] : null;
  }

  public function serverHost() {
    $arr = $this->config('server');
    return !empty($arr['host']) ? $arr['host'] : null;
  }

  public function serverPort() {
    $arr = $this->config('server');
    return !empty($arr['port']) ? $arr['port'] : null;
  }

  public function serverProtocol() {
    $arr = $this->config('server');
    return !empty($arr['protocol']) ? $arr['protocol'] : null;
  }

  public function serverAuthenticate() {
    $arr = $this->config('server');
    return !empty($arr['auth']) ? $arr['auth'] : null;
  }

  public function serverUsername() {
    $arr = $this->config('server');
    return !empty($arr['username']) ? $arr['username'] : null;
  }

  public function serverPassword() {
    $arr = $this->config('server');
    return !empty($arr['password']) ? $arr['password'] : null;
  }

  public function attachmentThreshold() {
    $ret = self::$attachment_treshold_default;
    try {
      if (!empty($this->config['attachment-threshold'])) {
        $ret = \ByteUnits\parse($this->config['attachment-threshold'])->numberOfBytes();
      }
    } catch (\Exception $e) {
      // @TODO: Log error
    }
    return $ret;
  }

  public function attachmentCompress() {
    $ret = true;
    if (isset($this->config['attachment-threshold'])) {
      $ret = $this->config['attachment-threshold'];
    }
    return $ret;
  }

}
