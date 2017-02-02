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
use bsuir\app\BSUIR;
use bsuir\drivers\Redis as User;
use bsuir\helpers\Phrase;

// init
$config   = json_decode(file_get_contents('info/config.json'));
$token    = $config->telegram->token;
$debugToken = $config->telegram->debugToken;

$bot      = new Bot($token);
$debugBot = new Bot($debugToken);
	list( $chat, $username, $name, $message, $message_id, $message_raw ) = $bot->returnMessageInfo( json_decode( file_get_contents( 'php://input' ) ) );


$user  = new User('info');
	
$reply = Phrase::getPhrase('command404');

$currentUser = $user->getCurrentUser($chat);
if ($currentUser)
{
    $userGroupID = BSUIR::getGroupID($currentUser['group_id']);
}

//act by message

if ($message == '/today') {
    if ($currentUser) {
        $date = BSUIR::getDate(time());
        $reply = BSUIR::parseSchedule(BSUIR::getGroupSchedule($userGroupID, $date['day'], $date['week']));
    } else {
        $reply = Phrase::getPhrase('user404');
    }
}

if ($message == 'ping') {
	$reply = $user->ping();
}

if ($message == '/date') {
	$date = BSUIR::getDate(time());
	$reply = "Сегодня ".$date['day']." день".PHP_EOL.$date['week']." недели".PHP_EOL;
}

if ($message == '/tomorrow') {
    if ($currentUser) {
        $date = BSUIR::getDate(strtotime('tomorrow'));
        $reply = BSUIR::parseSchedule(BSUIR::getGroupSchedule($userGroupID, $date['day'], $date['week']));
    } else {
        $reply = Phrase::getPhrase('user404');
    }
}

if ($message == '/get') {
    $reply = Phrase::getPhrase('get404');
}

if (preg_match('/^\/get [1-7] [1-4]/', $message)) {
    if ($currentUser) {
        $day  = substr($message, 5, 1);
        $week = substr($message, 7, 1);
        $reply = BSUIR::parseSchedule(BSUIR::getGroupSchedule($userGroupID, $day, $week));
    } else {
        $reply = Phrase::getPhrase('user404');
    }
}

if ($message == '/me')
{
    if ($currentUser)
    {
        $reply = json_encode($currentUser, JSON_UNESCAPED_UNICODE);
    }
}

if ($message == '/start') {
    if (!$currentUser || $currentUser['status'] == 0) {
        $reply = "Привет, $name!" . PHP_EOL . "Введи номер группы. 👆";
        $user->manageUser($chat, array(
            'gid' => 'temp',
            'username' => $username,
            'display_name' => $name,
            'status' => 1,
            'cron' => 1
        ));
        $bot->sendSticker($chat, 'BQADAgADQQADSEvvAQ1q8f_OrLAaAg');
    } else {
         if ($currentUser['group_id']) {
             $date = BSUIR::getDate();
             $reply = BSUIR::parseSchedule(BSUIR::getGroupSchedule($userGroupID, $date['day'], $date['week']));
         } else
             $reply = Phrase::getPhrase('group404');
    }
}

if (is_numeric($message)) {
        $reply = Phrase::getPhrase('groupSaved');
        $user->manageUser($chat, array(
            'gid' => $message,
            'username' => $username,
            'display_name' => $name,
            'status' => 2,
            'cron' => 1
        ));
}

if ((in_array(trim($message), Phrase::getPhrase('yes')) || in_array(trim($message), Phrase::getPhrase('no'))) && $currentUser['status'] > 1) {
    $cron  = (in_array(trim($message), Phrase::getPhrase('yes'))) ? "1" : "0";
    $reply = Phrase::getPhrase('settingsSaved');
    $user->manageUser($chat, array(
        'gid' => $currentUser['group_id'],
        'username' => $username,
        'display_name' => $name,
        'status' => 3,
        'cron' => $cron
    ));
}

if ($message == '/about') {
    $reply = 'Запилил Андрей М. (@Karavay)' . PHP_EOL . 'Пользователей: ' . $user->getUsersCount();
}

// end act by message


$bot->forwardMessage($bot->debugchat, $message_id, json_encode($message_raw, JSON_UNESCAPED_UNICODE));
$bot->sendMessage($chat, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message_raw, JSON_UNESCAPED_UNICODE));
