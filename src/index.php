<?php
date_default_timezone_set("Europe/Minsk");
require_once 'di.php';
use BSUIRBot\Model\Type\Type;
use BSUIRBot\Model\User;

$bugsnag = $container->get(Bugsnag\Client::class);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$input = file_get_contents( 'php://input' );
$input = json_decode($input);

if (!$input)
    die('Nothing to do here.');

$command = new Type($input);
$type = $command->getObjectType();
$commandList = $container->get('config')->getPermissions();
$database = $container->get(\BSUIRBot\Model\Database\Redis::class);
$parser = $container->get(\BSUIRBot\Model\Util\CommandParseHelper::class);

$user  = new User($database, $commandList, $parser);
$user->setLogger($bugsnag);
$chat = ($type === 'callback_query') ? $command->{$type}->getMessage()->getChat() : $command->{$type}->getChat();
$user->load($chat);

$bugsnag->registerCallback(function ($report) use ($user) {
    $report->setUser([
        'id' => $user->getId(),
        'name' => $user->getDisplayName(),
        'group_id' => $user->getGroupId(),
    ]);
});
$bot = $container->get(\BSUIRBot\Model\Bot\Telegram::class);
$schedule = $container->get(\BSUIRBot\Model\BSUIR::class);
$phrases = $container->get(\BSUIRBot\Model\Util\Phrase::class);


try {
    $Controller  = new \BSUIRBot\Controller\TelegramController( $command, $bot, $user, $schedule, $phrases, $parser );
    $Controller->setLogger($bugsnag);
    $Controller->execute();
} catch (\BreakException $breakException) {
    exit($breakException->returnMessage());
} catch (\Exception $e) {
    $reply = 'Идет апдейт бота, обратитесь чуть позже.'.PHP_EOL.'Дебаг инфо: '.$e->getMessage();
    $bugsnag->notifyException($e);
    $bot->sendMessage($user->getId(), $reply);
} catch (\Error $error) {
    $reply = 'Произошла ошибка в логике бота.'.PHP_EOL.'Инфо: '.$error->getMessage();
    $bugsnag->notifyException($error);
    $bot->sendMessage($user->getId(), $reply);
}
//
//$tgDebugToken = $container->get(\BSUIRBot\Config\ProdConfig::class)->getTGDebugToken();
//$bot = new Bot($tgDebugToken);
//
//$message = (object) $bot->returnMessageInfo( $input , (!empty($input->callback_query)) ? 'callback' : 'message' );
//$bot->sendMessage($message->user_id, $reply);
//$bot->forwardMessage($message->chat, $message->message_id, $reply);
//$debugBot->sendMessage($bot->debugchat, json_encode($message->message_raw, JSON_UNESCAPED_UNICODE));