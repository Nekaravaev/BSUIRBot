<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 20.11.17
 * Time: 2:23 PM
 */

namespace tests\fixtures;
use BSUIRBot\Config\Config;

class TestConfig extends Config
{
    /** @var string $TGtoken */
    protected $TGtoken = 'TG';
    /** @var string $VKtoken */
    protected $VKtoken = 'VK';

    public $environment = 'testing';

    protected $VKConfirmationCode = null;
}