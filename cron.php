<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 13.03.17
 * Time: 5:38 PM
 */
date_default_timezone_set("Europe/Minsk");
require __DIR__ . '/vendor/autoload.php';
use app\models\Cron;
use app\models\BSUIR;


BSUIR::updateGroups();
//Cron::writeToCronUsers();
