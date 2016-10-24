<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */
date_default_timezone_set("Europe/Minsk");
require_once "autoload.php";
// init
$bot      = new Bot('128735339:AAH1WyvktGZayrLDJe-SdeulXxGEEQaxN8M');
$debugBot = new Bot('89856014:AAGKnvayT242euRHofVyygmVODCjtEoJXEU');
list($chat, $username, $name, $message, $message_id, $message_raw) = $bot->returnMessageInfo(json_decode(file_get_contents('php://input')));
$user  = new User('info');
$schedule = new BSUIR();


$reply = date('[H:i:s]') . ' Команда не распознана.';
$currentUser = $user->getCurrentUser($chat);

$yes = array(
    'yes',
    'Yes',
    'Да',
    'да',
    'Да.',
    'да.',
    'yes.',
    'Yes.'
);
$no  = array(
    'no',
    'No',
    'Нет',
    'нет',
    'нет.',
    'Нет.',
    'no.',
    'No.',
    'Nope.'
);

//act by message
if ($message == '/today' || $message == '/today@BSUIRBot') {
    $date = $schedule->getDate();
    if ($currentUser) {
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($schedule->getGroupID($currentUser->group_id), $date['day'], $date['week']));
    } else {
        $reply = 'Тут такое дело' . PHP_EOL . 'Не могу найти тебя в базе :(' . PHP_EOL . 'Введи /start и пройди регистрацию ещё разок.';
    }
}

if ($message == '/tomorrow' || $message == '/tomorrow@BSUIRBot') {
    $date = $schedule->getDate(true);
    if ($currentUser) {
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($schedule->getGroupID($currentUser->group_id), $date['day'], $date['week']));
    } else {
        $reply = 'Тут такое дело' . PHP_EOL . 'Не могу найти тебя в базе :(' . PHP_EOL . 'Введи /start и пройди регистрацию ещё разок.';
    }
}

if ($message == '/get' || $message == '/get@BSUIRBot') {
    $reply = 'Немного не так.' . PHP_EOL . 'Используй по примеру /get [номер дня недели 1-7] [номер недели [1-4]' . PHP_EOL . '☝ ex: /get 1 4';
}

if (preg_match('/^\/get@BSUIRBot [1-7] [1-4]/', $message) || (preg_match('/^\/get [1-7] [1-4]/', $message))) {
    if ($currentUser) {
        if (preg_match('/^\/get@BSUIRBot [1-7] [1-4]/', $message)) {
            $day  = substr($message, 14, 1);
            $week = substr($message, 16, 1);
        } else {
            $day  = substr($message, 5, 1);
            $week = substr($message, 7, 1);
        }
        $reply = $schedule->parseSchedule($schedule->getGroupSchedule($schedule->getGroupID($currentUser->group_id), $day, $week));
    } else {
        $reply = 'Тут такое дело.' . PHP_EOL . 'Не могу найти тебя в базе :(' . PHP_EOL . 'Введи /start и пройди регистрацию ещё разок.';
    }

}

if ($message == '/group' || $message == '/group@BSUIRBot') {
    $reply = 'Ошибка!' . PHP_EOL . 'Вы забыли ввести номер группы.' . PHP_EOL . 'Так: /group номер_группы';
}

if ($message == '/me')
{
    if ($currentUser)
    {
        $reply = json_encode($currentUser);
    }
}

if ($message == '/start' || $message == '/start@BSUIRBot') {
    if (!$currentUser || $currentUser->{'status'} == 0) {
        $reply = "Привет, $name!" . PHP_EOL . "Введи номер группы. 👆";
        $user->manageUser($chat, array(
            'gid' => 'temp',
            'username' => $username,
            'display_name' => $name,
            'status' => 1,
            'cron' => 0
        ));
        $bot->sendSticker($chat, 'BQADAgADQQADSEvvAQ1q8f_OrLAaAg');
    } else {
         if ($currentUser->group_id) {
             $reply = $schedule->parseSchedule($schedule->getGroupSchedule($schedule->getGroupID($currentUser->group_id), date('w') - 1));
         } else
             $reply = 'Не могу найти твою группу.' . PHP_EOL .
                       'Может, все уже закончили, а ты не в курсе?' . PHP_EOL .
                       'Или введи /settings и настройся заново';
    }
}

if (is_numeric($message) || preg_match('/^\/group@BSUIRBot [1-9][0-9]{0,15}/', $message)) {
    if (preg_match('/^\/group@BSUIRBot [1-9][0-9]{0,15}/', $message)) {
        $group = substr($message, 16);
        $user->manageUser($chat, array(
            'gid' => $group,
            'username' => $username,
            'display_name' => $name,
            'status' => 2,
            'cron' => 0
        ));
        $reply = 'Теперь можно получать расписание через /today.';
    } else {
        $reply = '👍' . PHP_EOL . "Оповещать о расписании по утрам?";
        $user->manageUser($chat, array(
            'gid' => $message,
            'username' => $username,
            'display_name' => $name,
            'status' => 2,
            'cron' => 0
        ));
    }
}

if ($message == '/date' || $message == '/date@BSUIRBot') {
    $date  = $schedule->getDate();
    $reply = PHP_EOL . "WeekNumber: " . $date['week'] . PHP_EOL . "Day: " . $date['day'] . PHP_EOL . "Tomorrow: ";


    $date = $schedule->getDate(true);
    $reply .= PHP_EOL . "WeekNumber: " . $date['week'] . PHP_EOL . "Day: " . $date['day'] . PHP_EOL;
}

if ((in_array(trim($message), $yes) || in_array(trim($message), $no)) && $currentUser->{'status'} != 1) {
    $cron  = (in_array(trim($message), $yes)) ? true : false;
    $reply = '👍' . PHP_EOL . 'Настройки сохранены.' . PHP_EOL;
    $reply .= 'Доступные команды:' . PHP_EOL;
    $reply .= '/today - расписание на сегодня;' . PHP_EOL;
    $reply .= '/get числовой номер дня недели [номер недели] (пример: /get 1 4) - расписание по указанному критерию;' . PHP_EOL;
    $reply .= '/settings - смена группы и настройки крона;';
    $reply .= '/about - рандом инфа.';
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

$bot->forwardMessage($bot->debugchat, $message_id, json_encode($message_raw));
$bot->sendMessage($chat, $reply);
$debugBot->sendMessage($bot->debugchat, json_encode($message_raw));
