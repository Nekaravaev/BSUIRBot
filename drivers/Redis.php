<?php
/**
	 * Created by PhpStorm.
	 * User: karavaev
	 * Date: 22.11.16
	 * Time: 8:42 PM
	 */

namespace bsuir\drivers;

class Redis {

	public $redis;

	public function __construct() {
		$this->redis = new \Redis();
		$this->redis->connect('127.0.0.1', 6379);
	}

	public function ping() {
		return $this->redis->ping();
	}

}