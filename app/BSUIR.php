<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.48
 */

class BSUIR {

    public function getGroupSchedule($group_id, $dayweek = false, $week = false){
        $weekDays = array('Воскресенье', 'Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота');
        if (!$week) {
            $week = ceil((date("d") - date("w") - 1) / 7) + 1;
        }
        if(!$dayweek) {
            $today = $weekDays[date('w') - 2];
        } else {
            $today = $weekDays[$dayweek + 1];
        }


        $xml = simplexml_load_file("http://www.bsuir.by/schedule/rest/schedule/$group_id");
        foreach ($xml->scheduleModel as $day) {
            if($day->weekDay == $today){
                foreach ($day->schedule as $schedule) {
                    if(in_array($week, (array) $schedule->weekNumber)){
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

    public function getGroupID($group_name){
        $groups = json_decode(file_get_contents("info/groups.json"));
        foreach ($groups->studentGroup as $group){
            if ($group->name == $group_name) {
                $group_id = $group;
                break;
            }
        }
        return ($group_id) ? $group_id->id : false;
    }

    public function parseSchedule($lessons) {
        if ($lessons){
            $i = 0;
            $reply = '';
            foreach ($lessons as $lesson) {
                $i++;
                $sub = ($lesson['subgroup'] == 0) ? 'всех'  : $lesson['subgroup'].' подгруппы';
                $reply .= $i . ' пара ('.$lesson['time'].') - {'.$lesson['auditory'].'} : ['.$lesson['type'].'] '.
                    $lesson['name'].' у '.$lesson['employee'].' для '. $sub .PHP_EOL;
            }
        } else {
            $reply = 'Выходной';
        }
        return $reply;
    }


}
