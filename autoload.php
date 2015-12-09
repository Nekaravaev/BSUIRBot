<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.19
 */
$loadDir = 'app';
$classes = glob($loadDir.'/*.php');
foreach ($classes as $class) {
    require_once $class;
}
