<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 20.10.17
 * Time: 3:27 PM
 */

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Tests\\', __DIR__ . '/unit');
$loader->addPsr4('Fixtures\\', __DIR__ . '/fixtures');