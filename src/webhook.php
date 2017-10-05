<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 04.10.17
 * Time: 11:57 AM
 */
require '../vendor/autoload.php';
use app\models\bots\Telegram as Bot;
use app\Config;

$config = Config::getInstance();
$tgDebugToken = $config->getTGDebugToken();

$bot      = new Bot($tgDebugToken);
$request = $bot->sendRequest('telegram', [
    'method' => 'setWebhook',
    'token' => $tgDebugToken,
    'params' => http_build_query([
        'url' => ''
    ])
], false);

echo "Result: {$request->result}. {$request->description}";
