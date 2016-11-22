<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 12.9.15
 * Time: 1.02.
 */


class Cron
{
    public function __construct()
    {
        return $this->updateGroups();
    }

    protected function updateGroups()
    {
        $xml = simplexml_load_file('http://www.bsuir.by/schedule/rest/studentGroup');
        $groups = fopen('../info/groups.json', 'w');
        $result = fwrite($groups, json_encode($xml));
        fclose($groups);

        return $result;
    }
}

new Cron();

$userClass = new User('../info');
$schedule = new BSUIR('../info');
$bot = new Bot('128735339:AAH1WyvktGZayrLDJe-SdeulXxGEEQaxN8M');

$cronUsers = $userClass->getCronUsers();
$date = $schedule->getDate();

foreach ($cronUsers as $user) {
    $msg = 'Доброе утро, '.$user->{'display_name'}.PHP_EOL.
         'Сегодня твои занятия:'.PHP_EOL.$schedule->parseSchedule($schedule->getGroupSchedule($schedule->getGroupID($user->{'group_id'}), $date['day'], $date['week']));
    $bot->sendMessage($user->{'user_id'}, $msg);
}
