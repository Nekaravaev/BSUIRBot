<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
*/
error_reporting(0);
date_default_timezone_set("Europe/Minsk");
require __DIR__ . '/vendor/autoload.php';
use bsuir\app\VK as Bot;
use bsuir\app\BSUIR;
use bsuir\drivers\Redis as User;
use bsuir\helpers\Phrases;

header("HTTP/1.1 200 OK");
echo "ok";
// init
$config   = json_decode(file_get_contents('info/config.json'));
$token    = $config->vk->token;

$bot      = new Bot($token);
$request = json_decode(file_get_contents('php://input'));

$user  = new User('info');
$schedule = new BSUIR();
$phrase = new Phrases();

$reply = $phrase::getPhrase('command404');
$bsuirGroupId = $schedule->getGroupID("581062");

//act by message
if ($request->type == 'message_new') {
    list($chat, $name, $title, $message, $messageType, $message_raw) = $bot->returnMessageInfo($request, $request->type);

    if ($message == '/today') {
        $date = $schedule->getDate(time());
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($bsuirGroupId, $date['day'], $date['week']));
    }

    if ($message == '/date') {
        $date = $schedule->getDate(time());
        $reply = "Сегодня " . $date['day'] . " день" . PHP_EOL . $date['week'] . " недели" . PHP_EOL;
    }

    if ($message == '/tomorrow') {
        $date = $schedule->getDate(true);
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($bsuirGroupId, $date['day'], $date['week']));
    }

    if ($message == '/get') {
        $reply = $phrase::getPhrase('get404');
    }

    if (preg_match('/^\/get [1-7] [1-4]/', $message)) {
        $day = substr($message, 5, 1);
        $week = substr($message, 7, 1);
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($bsuirGroupId, $day, $week));
    }

    if ($message == '/about') {
        $reply = 'Запилил Андрей М. (@Karavay)';
    }

    $bot->sendMessage($chat, $reply);
    // end act by message
}

if ($request->type == 'message_allow') {
    list($chat, $name, $message_raw) = $bot->returnMessageInfo($request, $request->type);

    $usersArray = json_decode(file_get_contents('info/updateUsers.json'), true);

    array_push($usersArray, $chat);
    $updateUsers = fopen('info/updateUsers.json', 'w');
    $result = fwrite($updateUsers, json_encode($usersArray, JSON_PRETTY_PRINT));
    fclose($updateUsers);

    $reply = $name. ', ты подписан на обновления стены.';
    $bot->sendMessage($chat, $reply);
}

if ($request->type == 'message_deny') {
    list($chat, $name, $message_raw) = $bot->returnMessageInfo($request, $request->type);

    $usersArray = json_decode(file_get_contents('info/updateUsers.json'), true);

    if (($key = array_search($chat, $usersArray)) !== false) {
        unset($usersArray[$key]);
    }

    $updateUsers = fopen('info/updateUsers.json', 'w');
    $result = fwrite($updateUsers, json_encode($usersArray, JSON_PRETTY_PRINT));
    fclose($updateUsers);

    $reply = $name. ', ты выпилен из списка получения обновлений стены.';

    $bot->sendMessage($chat, $reply);
}

if ($request->type == 'wall_post_new') {
    list($postId, $groupId, $fromId, $ownerId, $message_raw) = $bot->returnMessageInfo($request, $request->type);
    $usersArray = json_decode(file_get_contents('info/updateUsers.json'), true);

    $reply = json_encode($usersArray);

    $bot->sendMessage(19857747, $reply);
}
