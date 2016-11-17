<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.48
 */

class BSUIR {


    public function __construct($folder = 'info')
    {
        if( !empty ( $folder ) )
        {
            $this->folder = $folder;
        }
    }

    public function getDate($tommorow = false)
    {
		$date = ($tommorow) ? date('d.m.Y', strtotime('tomorrow')) : date('d.m.Y');
	    $weekNumber = file_get_contents("https://www.bsuir.by/schedule/rest/currentWeek/date/$date");
        $dayNumber = ($tommorow) ? date('w', strtotime('tomorrow')) : date('w');

        return array(
            'week' => $weekNumber,
            'day'  => $dayNumber
        );
    }

    public function getGroupSchedule($group_id, $day = false, $week = false)
    {
        $todaySubjects = array();
        $weekDays = array('Воскресенье', 'Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота');
        $today = $weekDays[trim($day)];
        $week = trim($week);

        $xml = simplexml_load_file("http://www.bsuir.by/schedule/rest/schedule/$group_id");
        foreach ($xml->scheduleModel as $singleDay) {
            if($singleDay->weekDay == $today){
                foreach ($singleDay->schedule as $schedule) {
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

    public function getGroupID($group_name)
    {
        $groups = json_decode(file_get_contents($this->folder."/groups.json"));
        foreach ($groups->studentGroup as $group){
            if ($group->name == $group_name) {
                $group_id = $group;
                break;
            }
        }
        return ($group_id) ? $group_id->id : false;
    }

    public function parseSchedule($lessons)
    {
        if ($lessons)
        {
            $i = 0;
            $reply = '';
            foreach ($lessons as $lesson) {
                $i++;
                $sub = ($lesson['subgroup'] == 0) ? 'всех'  : $lesson['subgroup'].' подгруппы';
                $reply .= $i . ' пара ('.$lesson['time'].') - {'.$lesson['auditory'].'} : ['.$lesson['type'].'] '.
                    $lesson['name'].' у '.$lesson['employee'].' для '. $sub .PHP_EOL;
            }
        } else
        {
            $reply = 'Выходной';
        }
        return $reply;
    }


}