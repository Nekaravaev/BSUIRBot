<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.48
 */

namespace bsuir\app;

class BSUIR
{
    public static $folder = 'info';


    /**
     * Fetch number of student's week through API
     *
     * @param $timestamp int timestamp
     *
     * @return array result: week & day
     */

    public static function getDate($timestamp)
    {
        $weekNumber = file_get_contents('https://www.bsuir.by/schedule/rest/currentWeek/date/' . date('d.m.Y', $timestamp));
        $dayNumber = date('w', $timestamp);

        return [
            'week' => $weekNumber,
            'day'  => $dayNumber
        ];
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

        $xml = simplexml_load_file("http://www.bsuir.by/schedule/rest/schedule/$gID");
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
     */

    public static function getGroupID($gID)
    {
        $groups = json_decode(file_get_contents(self::$folder."/groups.json"));
        foreach ($groups->studentGroup as $group) {
            if ($group->name == $gID) {
                $studentGroup = $group;
                break;
            }
        }
        return ($studentGroup) ? $studentGroup->id : false;
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
            $i = 0;
            foreach ($lessons as $lesson) {
                $i++;
                $sub = ($lesson['subgroup'] == 0) ? 'всех'  : $lesson['subgroup'].' подгруппы';
                $reply .= $i . ' пара ('.$lesson['time'].') - {'.$lesson['auditory'].'} : ['.$lesson['type'].'] '.
                    $lesson['name'].' у '.$lesson['employee'].' для '. $sub .PHP_EOL;
            }
        }
        return (!empty($reply)) ? $reply : 'Выходной';
    }
}
