<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 12.10.17
 * Time: 12:58 PM
 */
namespace BSUIRBot\Model;
use BSUIRBot\Exception\DependencyException;

class DIContainer {

    private $_registeredServices = [];
    private $_registeredFactories = [];


    public function register($name, callable $factory) {
        if (!isset($this->_registeredFactories[$name])) {
            $this->_registeredFactories[$name] = $factory;
        }
    }

    public function get($name) {
        if (isset($this->_registeredServices[$name])) {
            return $this->_registeredServices[$name];
        }
        if (isset($this->_registeredFactories[$name])) {
            $this->_registeredServices[$name] = call_user_func($this->_registeredFactories[$name], $this);
            return $this->_registeredServices[$name];
        }

        throw new DependencyException('Not found.');
    }
}