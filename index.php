<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */
date_default_timezone_set("Europe/Minsk");
require __DIR__ . '/vendor/autoload.php';
use app\models\bots\Telegram as Bot;
use app\Config;
use app\controllers\Controller;

$bot      = new Bot(Config::getTGtoken());
$debugBot = new Bot(Config::getTGDebugToken());
$input =  json_decode(file_get_contents( 'php://input' ));
list( $chat, $username, $name, $message, $messageId, $message_raw ) = $bot->returnMessageInfo( $input , 'message' );

try {
    $Controller  = new Controller( $input );
    $action = $Controller->parseMessage();
    $bot->sendMessage($action->chat, $action->reply, $action->keyboard);
    $debugBot->sendMessage($bot->debugchat, json_encode($message_raw, JSON_UNESCAPED_UNICODE));
    exit($action->reply);
} catch (Exception $e) {
    $reply = 'Идет апдейт бота, обратитесь чуть позже.'.PHP_EOL.'Дебаг инфо: '.$e->getMessage();
} catch (Error $error) {
    $reply = 'Произошла ошибка в логике бота.'.PHP_EOL.'Инфо: '.$error->getMessage();
}
$bot->sendMessage($chat, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message_raw, JSON_UNESCAPED_UNICODE));