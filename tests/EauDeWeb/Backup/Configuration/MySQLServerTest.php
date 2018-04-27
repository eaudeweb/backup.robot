<?php

namespace Tests\EauDeWeb\Backup\Configuration;

use EauDeWeb\Backup\Configuration\MySQLServer;

final class MySQLServerTest extends TestBase {

  /** @var MySQLServer */
  private $srv1 = null;
  /** @var MySQLServer */
  private $srv2 = null;
  /** @var MySQLServer */
  private $srv3 = null;

  public function setUp() {
    parent::setUp();
    $projects = $this->config->getProjects();
    /** @var \EauDeWeb\Backup\Configuration\Project $p1 */
    $p1 = $projects['project1'];
    $servers = $p1->getMySQLServers();
    $this->srv1 = $servers['server1'];
    $this->srv2 = $servers['server2'];
    $this->srv3 = $servers['server3'];
  }

  public function testId() {
    $this->assertEquals('server1', $this->srv1->id());
    $this->assertEquals('server2', $this->srv2->id());
  }

  public function testPort() {
    $this->assertEquals(3306, $this->srv1->port());
    $this->assertEquals(1306, $this->srv2->port());
    $this->assertEquals(3306, $this->srv3->port());
  }

  // public function testValidateMysqliExtension() {
  // @todo
  // }

  public function testBackupDestination() {
    $this->assertEquals('/tmp/backup-robot-test/test/silo1/', $this->srv1->backupDestination());
    $this->assertEquals('/tmp/backup-robot-test/test/silo2/', $this->srv2->backupDestination());
  }

  public function testUser() {
    $this->assertEquals('root1', $this->srv1->user());
    $this->assertEquals('root2', $this->srv2->user());
  }

  public function testPassword() {
    $this->assertEquals('pass1', $this->srv1->password());
    $this->assertEquals('pass2', $this->srv2->password());
  }

  public function testHost() {
    $this->assertEquals('silo1.company.com', $this->srv1->host());
    $this->assertEquals('silo2.company.com', $this->srv2->host());
  }

  // public function testDatabases() {
  // @todo
  // }

  // public function testPrepare() {
  // @todo
  // }

  // public function testCreate() {
  // @todo
  // }

  // public function testValidate() {
  // @todo
  //}

  // public function testValidateConnection() {
  // @todo
  // }

  // public function testConnect() {
  // @todo
  // }

  public function testGzip() {
    $this->assertTrue($this->srv1->gzip());
    $this->assertFalse($this->srv2->gzip());
    $this->assertTrue($this->srv3->gzip());
  }

  // public function testValidateBackupWritable() {
  // @todo
  // }

  // public function testDatabasesToBackup() {
  // @todo
  //}

  public function testBlacklist() {
    $this->assertArraySubset(["performance_schema", "information_schema", "db1"], $this->srv1->blacklist());
    $this->assertArraySubset(["performance_schema", "information_schema"], $this->srv2->blacklist());

  }

  // public function testSocket() {}
}
