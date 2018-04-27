<?php
/**
 * Created by PhpStorm.
 * User: cristiroma
 * Date: 4/23/18
 * Time: 3:11 PM
 */

namespace Tests\EauDeWeb\Backup\Configuration;


class RsyncTest extends TestBase {

  /** @var \EauDeWeb\Backup\Configuration\Rsync */
  private $task1 = null;
  /** @var \EauDeWeb\Backup\Configuration\Rsync */
  private $task2 = null;

  public function setUp() {
    parent::setUp();
    $projects = $this->config->getProjects();
    /** @var \EauDeWeb\Backup\Configuration\Project $p1 */
    $p1 = $projects['project1'];
    $this->task1 = $p1->getRsyncTasks()['full'];
    $this->task2 = $p1->getRsyncTasks()['defaults'];
  }

  public function testPort() {
    $this->assertEquals('2279', $this->task1->port());
    $this->assertEquals('22', $this->task2->port());
  }

  // public function testValidate() {
  // @todo
  // }

  // public function testCreate() {
  // @todo
  // }

  // public function testConfig() {
  // @todo
  // }

  public function testHost() {
    $this->assertEquals('backup-push-storage.company.com', $this->task1->host());
  }

  // public function testValidateConnection() {
  // @todo
  // }

  public function testTo() {
    $this->assertEquals('/backups/PROJECT1/databases', $this->task1->to());
  }

  public function testFrom() {
    $this->assertEquals('/tmp/backup-robot-test/test/databases/', $this->task1->from());
  }

  public function testUser() {
    $this->assertEquals('bofh', $this->task1->user());
  }

  public function testFakeSuper() {
    $this->assertTrue($this->task1->fakeSuper());
    $this->assertFalse($this->task2->fakeSuper());
  }

  // public function testValidateLocalRsync() {
  // @todo
  // }
}
