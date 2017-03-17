<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.48
 */

namespace app\models;
use app\helpers\Curl;
use PHPUnit\Framework\Error\Error;

class BSUIR
{
    public static $params = [];

    /**
     * Update groupList in local groups.json file
     * @throws \Error if XML data are not available
     * @return bool|int false if no data was write, int if success
     */

    public static function updateGroups()
    {
        $xml = simplexml_load_file('https://www.bsuir.by/schedule/rest/studentGroup');
        $groups = fopen(__DIR__.'/../../info/groups.json', 'w');
        if ($xml) {
            $result = fwrite($groups, json_encode($xml, JSON_PRETTY_PRINT));
            fclose($groups);
        } else {
            throw new \Error('No groups are fetched from BSUIR');
        }

        return ($result) ? true : false;
    }


    /**
     * Fetch number of student's week through API
     *
     * @param $timestamp int timestamp
     *
     * @return array result: week & day
     */

    public static function getDate($timestamp)
    {
        $date = date('d.m.Y', $timestamp);

        preg_match("/\d/i", Curl::getData('https://www.bsuir.by/schedule/rest/currentWeek/date/' . urlencode($date), self::$params), $weekNumber);

        $dayNumber = date('w', $timestamp);

        return [
            'week' => $weekNumber[0],
            'day'  => $dayNumber
        ];
    }

    /**
     * This is helper for retrieve of day name for inline buttons in bot
     * @param $dayNumber integer day number
     * @return string day name
     */

    public static function getDayNameByNumber($dayNumber)
    {
        $weekDays = ['Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];

        return $weekDays[$dayNumber];
    }

    /**
     * @param $gID string|int group number
     * @param $day int number of day in week
     * @param $week int number of student's week
     * @return array schedule for group
     */

    public static function getGroupSchedule($gID, $day, $week)
    {
        $todaySubjects = [];
        $weekDays = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота'];
        $today = $weekDays[trim($day)];
        $week = trim($week);
        $xmlRest = Curl::getData("https://www.bsuir.by/schedule/rest/schedule/".urlencode($gID), self::$params);

        $xml = simplexml_load_string($xmlRest);
        foreach ($xml->scheduleModel as $singleDay) {
            if ($singleDay->weekDay == $today) {
                foreach ($singleDay->schedule as $schedule) {
                    if (in_array($week, (array) $schedule->weekNumber)) {
                        $todaySubjects[] = array(
                            'name' => $schedule->subject,
                            'type' => $schedule->lessonType,
                            'time' => $schedule->lessonTime,
                            'auditory' => $schedule->auditory,
                            'subgroup' => $schedule->numSubgroup,
                            'employee' => $schedule->employee->firstName .' '. $schedule->employee->lastName
                        );
                    }
                }
            }
        }
        return $todaySubjects;
    }

    /**
     * Getting group ID in BSUIR's api by group of user
     *
     * @param $gID string|int number of group
     * @return bool|int id or unsuccessful result
     * @throws \Error if group not found
     */

    public static function getGroupID($gID)
    {
        $studentGroup = null;
        $groups = json_decode(file_get_contents(self::$folder."/groups.json"));
        foreach ($groups->studentGroup as $group) {
            if ($group->name == $gID) {
                $studentGroup = $group;
                break;
            }
        }
        if (empty($studentGroup)) throw new \Error('Группа не найдена. Введите другую.');
        return $studentGroup->id;
    }

    /**
     * Parse array with lessons after getting through getGroupSchedule
     *
     * @param $lessons array array with lessons to parse
     *
     * @return string string with message to user
     */

    public static function parseSchedule($lessons)
    {
        $reply = '';
        if (!empty($lessons)) {
            $lessonsCount = 0;
            foreach ($lessons as $lesson) {
                $lessonsCount++;
                $sub = ($lesson['subgroup'] == 0) ? 'всех'  : $lesson['subgroup'].' подгруппы';
                $reply .= $lessonsCount . ' пара ('.$lesson['time'].') - {'.$lesson['auditory'].'} : ['.$lesson['type'].'] '.
                    $lesson['name'].' у '.$lesson['employee'].' для '. $sub .PHP_EOL;
            }
        }
        return (!empty($reply)) ? $reply : 'Выходной';
    }
}
