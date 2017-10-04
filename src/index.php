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
use app\controllers\TelegramController as Controller;
use app\errors\BreakException;

$input =  json_decode(file_get_contents( 'php://input' ));

try {
    $Controller  = new Controller( $input );
    $action = $Controller->parseMessage();
    if ($Controller->message->type == 'callback'){
        $Controller->bot->answerCallbackQuery($action->message->callback_id, $action->reply);
        $send = $Controller->bot->editMessageText($action->message->chat_id, $action->message->message_id, $action->reply, $action->keyboard);
        //$mark = $Controller->bot->editMessageReplyMarkup($action->message->chat_id, $action->message->message_id, $action->keyboard);
    } else
        $send = $Controller->bot->sendMessage($action->chat, $action->reply, $action->keyboard);

    $Controller->debugBot->sendMessage($Controller->bot->debugchat, json_encode($action->message->message_raw, JSON_UNESCAPED_UNICODE));
    $forward = $Controller->bot->forwardMessage($action->message->chat_id, $action->message->message_id, $action->reply);

    if ($send->ok && $forward->ok)
        exit($action->reply);
    else {
        if (!$send->ok)
            $reply = $send->description;
        else
            $reply = $forward->description;
    }

} catch (BreakException $breakException) {
    exit($breakException->returnMessage());
} catch (\Exception $e) {
    $reply = 'Идет апдейт бота, обратитесь чуть позже.'.PHP_EOL.'Дебаг инфо: '.$e->getMessage();
} catch (\Error $error) {
    $reply = 'Произошла ошибка в логике бота.'.PHP_EOL.'Инфо: '.$error->getMessage();
}
$bot      = new Bot(Config::getTGtoken());
$debugBot = new Bot(Config::getTGDebugToken());

$message = (object) $bot->returnMessageInfo( $input , (!empty($input->callback_query)) ? 'callback' : 'message' );
$bot->sendMessage($message->user_id, $reply);
$bot->forwardMessage($message->chat, $message->message_id, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message->message_raw, JSON_UNESCAPED_UNICODE));