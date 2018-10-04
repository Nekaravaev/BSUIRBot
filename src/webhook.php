<?php
require "di.php";
$tgToken = $container->get('config')->getTGtoken();
$bot = $container->get(\BSUIRBot\Model\Bot\Telegram::class);

$request = $bot->sendRequest('telegram', [
    'method' => 'setWebhook',
    'token'  => $tgToken,
    'params' => http_build_query([
        'url' => ''
    ])
]);

echo "Result: {$request->result}. {$request->description}";
