<?php

namespace Tests\EauDeWeb\Backup\Configuration;

use EauDeWeb\Backup\Configuration\Configuration;
use EauDeWeb\Backup\Configuration\Email;

final class EmailTest extends TestBase {

  /** @var \EauDeWeb\Backup\Configuration\Email */
  private $email;

  public function setUp() {
    parent::setUp();
    $this->email = $this->config->getDefaultEmail();
  }

  public function testCreateMessage() {
    $msg = $this->email->createMessage('test');
    $this->assertNotNull($msg);
  }

  public function testCreateEmailBackupReport() {
    $msg = $this->email->createEmailBackupReport('test', '/path/to/file.log');
    $this->assertNotNull($msg);
  }

  public function testTo() {
    $this->assertEquals('destination.email@company.com', $this->email->to());
  }

  public function testServerProtocol() {
    $this->assertEquals('ssl', $this->email->serverProtocol());
  }

  public function testAttachmentThreshold() {
    $this->assertEquals(3000000, $this->email->attachmentThreshold());
  }

  public function testSubjectFail() {
    $this->assertEquals('[FAIL][SERVER-PROD01] Backup failed', $this->email->subjectFail());
  }

  public function testServerType() {
    $this->assertEquals('smtp', $this->email->serverType());
  }

  public function testServerUsername() {
    $this->assertEquals('backup', $this->email->serverUsername());
  }

  public function testServerHost() {
    $this->assertEquals('mail.company.com', $this->email->serverHost());
  }

  public function testServerDebugLevel() {
    $this->assertEquals(2, $this->email->serverDebugLevel());
  }

  public function testServerPassword() {
    $this->assertEquals('secret', $this->email->serverPassword());
  }

  public function testCreate() {
    $email = Email::create(['key' => 'value']);
    $this->assertNotNull($email);
  }

  public function testServerPort() {
    $this->assertEquals(465, $this->email->serverPort());
  }

  public function testIsEnabled() {
    $this->assertTrue($this->config->getDefaultEmail()->isEnabled());

    $config = Configuration::create(['defaults' => ['email' => ['enabled' => false]]]);
    $this->assertFalse($config->getDefaultEmail()->isEnabled());
  }

  public function testSubjectSuccess() {
    $this->assertEquals('[OK][SERVER-PROD01] Backup success', $this->config->getDefaultEmail()->subjectSuccess());
  }

  public function testAttachmentCompress() {
    $this->assertTrue($this->config->getDefaultEmail()->attachmentCompress());

    $config = Configuration::create(['defaults' => ['email' => ['attachment-compress' => false]]]);
    $this->assertFalse($config->getDefaultEmail()->attachmentCompress());
  }

  public function testFrom() {
    $this->assertEquals('backup@company.com', $this->email->from());
  }

  public function testServerAuthenticate() {
    $this->assertTrue($this->config->getDefaultEmail()->serverAuthenticate());

    $config = Configuration::create(['defaults' => ['email' => ['server' => ['auth' => false]]]]);
    $this->assertFalse($config->getDefaultEmail()->serverAuthenticate());
  }

  public function testGetDefaultEmail() {
    $email = Configuration::get()->getDefaultEmail();
    $this->assertNotNull($email);
  }

}
