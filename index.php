<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */
date_default_timezone_set("Europe/Minsk");

require __DIR__ . '/vendor/autoload.php';
use bsuir\app\Bot;
use bsuir\app\BSUIR;
use bsuir\drivers\Redis as User;
use bsuir\helpers\Phrases;

// init
$bot      = new Bot('128735339:AAH1WyvktGZayrLDJe-SdeulXxGEEQaxN8M');
$debugBot = new Bot('89856014:AAGKnvayT242euRHofVyygmVODCjtEoJXEU');
list($chat, $username, $name, $message, $message_id, $message_raw) = $bot->returnMessageInfo(json_decode(file_get_contents('php://input')));

$user  = new User('info');
$schedule = new BSUIR();
$phrase = new Phrases();
	
$reply = $phrase::getPhrase('command404');

$currentUser = $user->getCurrentUser($chat);
if ($currentUser)
{
    $userGroupID = $schedule->getGroupID($currentUser->group_id);
}

//act by message

if ($message == '/today') {
    if ($currentUser) {
        $date = $schedule->getDate();
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($userGroupID, $date['day'], $date['week']));
    } else {
        $reply = $phrase::getPhrase('user404');
    }
}

if ($message == 'ping') {
	$reply = $user->ping();
}

if ($message == '/date') {
	$date = $schedule->getDate();
	$reply = "Сегодня ".$date['day']." день".PHP_EOL.$date['week']." недели".PHP_EOL;
}

if ($message == '/tomorrow') {
    if ($currentUser) {
        $date = $schedule->getDate(true);
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($userGroupID, $date['day'], $date['week']));
    } else {
        $reply = $phrase::getPhrase('user404');
    }
}

if ($message == '/get') {
    $reply = $phrase::getPhrase('get404');
}

if (preg_match('/^\/get [1-7] [1-4]/', $message)) {
    if ($currentUser) {
        $day  = substr($message, 5, 1);
        $week = substr($message, 7, 1);
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($userGroupID, $day, $week));
    } else {
        $reply = $phrase::getPhrase('user404');
    }
}

if ($message == '/me')
{
    if ($currentUser)
    {
        $reply = json_encode($currentUser);
    }
}

if ($message == '/start') {
    if (!$currentUser || $currentUser->{'status'} == 0) {
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
         if ($currentUser->group_id) {
             $date = $schedule->getDate();
             $reply = $schedule->parseSchedule($schedule->getGroupSchedule($userGroupID, $date['day'], $date['week']));
         } else
             $reply = $phrase::getPhrase('group404');
    }
}

if (is_numeric($message)) {
        $reply = $phrase::getPhrase('groupSaved');
        $user->manageUser($chat, array(
            'gid' => $message,
            'username' => $username,
            'display_name' => $name,
            'status' => 2,
            'cron' => 1
        ));
}

if ((in_array(trim($message), $phrase::getPhrase('yes')) || in_array(trim($message), $phrase::getPhrase('no'))) && $currentUser->{'status'} > 1) {
    $cron  = (in_array(trim($message), $phrase::getPhrase('yes'))) ? "1" : "0";
    $reply = $phrase::getPhrase('settingsSaved');
    $user->manageUser($chat, array(
        'gid' => $currentUser->{'group_id'},
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

// here we start to send msgs

$bot->forwardMessage($bot->debugchat, $message_id, json_encode($message_raw, JSON_UNESCAPED_UNICODE));
$bot->sendMessage($chat, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message_raw, JSON_UNESCAPED_UNICODE));
