<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 20.11.17
 * Time: 2:20 PM
 */
namespace tests\unit;

use PHPUnit\Framework\TestCase;
use tests\fixtures\TestConfig;

class ConfigTest extends TestCase {

    /** @var TestConfig $config */
    private $config;

    public function setUp()
    {
        $this->config = new TestConfig();
    }

    public function testGetTGtoken() {
        $token = $this->config->getTGtoken();
        $this->assertEquals('TG', $token);
    }

    public function testGetPermissions() {
        $permissions = $this->config->getPermissions();
        $this->assertTrue(is_array($permissions));
    }
    
}