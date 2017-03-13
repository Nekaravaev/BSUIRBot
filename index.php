<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */
date_default_timezone_set("Europe/Minsk");
require __DIR__ . '/vendor/autoload.php';
use bsuir\app\Telegram as Bot;
use bsuir\app\Config;
use bsuir\app\Controller;

$bot      = new Bot(Config::getTGtoken());
$debugBot = new Bot(Config::getTGDebugToken());
$input =  file_get_contents( 'php://input' );
list( $chat, $username, $name, $message, $messageId, $message_raw ) = $bot->returnMessageInfo( json_decode( $input ), 'message' );

try {
    $Controller  = new Controller( $input );
    $reply = $Controller->parseMessage();
} catch (Exception $e) {
    $reply = 'Идет апдейт бота, обратитесь чуть позже.'.PHP_EOL.'Дебаг инфо: '.$e->getMessage();
} catch (Error $error) {
    $reply = 'Произошла ошибка в логике бота.'.PHP_EOL.'Инфо: '.$error->getMessage();
}

echo $reply;
$bot->sendMessage($chat, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message_raw, JSON_UNESCAPED_UNICODE));