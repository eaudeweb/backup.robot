<?php


namespace Tests\EauDeWeb\Backup\Configuration;

use EauDeWeb\Backup\Configuration\Configuration;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use PHPUnit\Framework\TestCase;
use Robo\Robo;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;


class TestBase extends TestCase implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /** @var \EauDeWeb\Backup\Configuration\Configuration */
  protected $config;

  public function setUp() {
    $configFilePath = dirname(__DIR__) . '/../../../robo.test.yml';
    $configFile = realpath($configFilePath);
    if (empty($configFile)) {
      $this->fail("Missing configuration file: {$configFilePath}");
    }
    $config = Robo::createConfiguration([$configFile]);
    $input = new StringInput('');
    $container = Robo::createDefaultContainer($input, new NullOutput(), NULL, $config);
    $this->setContainer($container);
    $this->config = Configuration::create(Robo::config()->get('backup'));
  }

  public function testConfiguration() {
    $this->assertNotNull($this->config);
    $this->assertNotNull(Configuration::get());
  }

  public function testGetProjects() {
    $projects = $this->config->getProjects();
    $this->assertEquals(2, count($projects));
    $ob1 = $projects['project1'];
    $this->assertNotNull($ob1);
    $ob2 = $projects['project2'];
    $this->assertNotNull($ob2);
  }

}
