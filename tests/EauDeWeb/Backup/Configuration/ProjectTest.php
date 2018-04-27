<?php
/**
 * Created by PhpStorm.
 * User: cristiroma
 * Date: 4/23/18
 * Time: 2:52 PM
 */

namespace Tests\EauDeWeb\Backup\Configuration;

use EauDeWeb\Backup\Configuration\Project;

class ProjectTest extends TestBase {

  private $projects = [];

  public function setUp() {
    parent::setUp();
    $this->projects = $this->config->getProjects();
  }

  public function testGetMySQLServers() {
    /** @var Project $p1 */
    $p1 = $this->projects['project1'];
    $items = $p1->getMySQLServers();
    $this->assertEquals(3, count($items));
    $this->assertArrayHasKey('server1', $items);
    $this->assertArrayHasKey('server2', $items);
  }

  public function testGetRsyncTasks() {
    /** @var Project $p1 */
    $p1 = $this->projects['project1'];
    $items = $p1->getRsyncTasks();
    $this->assertEquals(2, count($items));
    $this->assertArrayHasKey('full', $items);
  }

}
