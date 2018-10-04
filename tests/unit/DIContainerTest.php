<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 20.11.17
 * Time: 12:29 PM
 */
namespace tests\unit;

use BSUIRBot\Model\DIContainer;
use PHPUnit\Framework\TestCase;

class DIContainerTest extends TestCase {

    /** @var DIContainer $class */
    private $class;

    public function setUp() {
        $this->class = new DIContainer();
    }

    public function testRegister() {
        $register = $this->class->register('test', function () {
            return true;
        });

        $this->assertTrue($register);
    }

    public function testGet() {
        $this->class->register('test', function () {
            return true;
        });

        $get = $this->class->get('test');

        $this->assertTrue($get);
    }

    /**
     * @expectedException \BSUIRBot\Exception\DependencyException
     */
    public function testGetNotRegisteredDependency() {
        $this->assertTrue($this->class->get('failure_dependency'));
    }

    /**
     * @expectedException \BSUIRBot\Exception\DependencyException
     */
    public function testDuplicateRegister() {
        $this->class->register('test', function () {
           return true;
        });

        $this->class->register('test', function () {
           return false;
        });
    }

}