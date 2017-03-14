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


$debugBot->sendMessage($bot->debugchat, json_encode($input, JSON_UNESCAPED_UNICODE));

$message = (object) $bot->returnMessageInfo( $input , (!empty($input->callback_query)) ? 'callback' : 'message' );

try {
    $Controller  = new Controller( $input );
    $action = $Controller->parseMessage();
    if ($message->type == 'callback'){
        $bot->answerCallbackQuery($action->callback_id, $action->reply);
        $send = $bot->editMessageReplyMarkup($action->chat, $message->message_id, $action->keyboard);
    } else
        $send = $bot->sendMessage($action->chat, $action->reply, $action->keyboard);

    $debugBot->sendMessage($bot->debugchat, json_encode($message->message_raw, JSON_UNESCAPED_UNICODE));
    $bot->forwardMessage($message->user_id, $message->message_id, $action->reply);

    if ($send->ok)
        exit($action->reply);
    else
        $reply = $send->description;

} catch (Exception $e) {
    $reply = 'Идет апдейт бота, обратитесь чуть позже.'.PHP_EOL.'Дебаг инфо: '.$e->getMessage();
} catch (Error $error) {
    $reply = 'Произошла ошибка в логике бота.'.PHP_EOL.'Инфо: '.$error->getMessage();
}
$bot->sendMessage($message->user_id, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message->message_raw, JSON_UNESCAPED_UNICODE));