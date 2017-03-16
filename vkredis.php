<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 16.03.17
 * Time: 1:44 PM
 */

date_default_timezone_set("Europe/Minsk");
require __DIR__ . '/vendor/autoload.php';

use app\drivers\Redis;
use app\models\bots\VK;

$array = [
  '19857747' => [

  ],
  '36805342' => [

  ],
  '113919892' => [

  ],
  '28541902' => [

  ],
  '8746118'  => [

  ],
  '232706073' => [

  ],
  '119530106' => [

  ],
  '204267778' => [

  ],
  '99173625' => [

  ],
  '384123614' => [

  ],
  '68764001' => [

  ],
  '153760291' => [

  ],
  '16917988' => [

  ]
];

$VK = new VK(\app\Config::getVKtoken());
$Redis = new Redis();

foreach ($array as $key => $value)
{
    $newArray = $value;

    $newArray['display_name'] = $VK->getDisplayName($key);
    $newArray['user_id'] = $key;

    $Redis->addToUpdatesVKGroup($key, $newArray);
}