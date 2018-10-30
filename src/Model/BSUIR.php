<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.48
 */

namespace BSUIRBot\Model;

use BSUIRBot\Exception\SchedulesNotFoundException;

class BSUIR
{
    public static $params = [];
    /** @var Request */
    protected $request;


    public function __construct($requestObject)
    {
        $this->request = $requestObject;
    }

    /**
     * Fetch number of student's week through API
     *
     * @param int $timestamp current timestamp
     *
     * @return array result: day & week
     */

    public function getDayAndWeekByDate(int $timestamp): array
    {
        $timezone = new \DateTimeZone("Europe/Minsk");

        $today = new \DateTime("now", $timezone);

        $requestDate = new \DateTime();
        $requestDate->setTimezone($timezone);
        $requestDate->setTimestamp($timestamp);

        $differenceBeforeDates = $requestDate->diff($today);

        $currentWeekNumber = (int) $this->request->send('http://students.bsuir.by/api/v1/week');

        if ($differenceBeforeDates->days > 0) {
            $currentWeekDay = (int) $today->format('w');

            $daysOverCurrentWeek = abs((7 - ($currentWeekDay + $differenceBeforeDates->days )));
            if ( $daysOverCurrentWeek > 0) {
                $weeksCount = (round($daysOverCurrentWeek/7) == 0) ? : 1;
                $weeksCount += $currentWeekNumber;
                $currentWeekNumber = (floor($weeksCount / 4) === 0) ? : 1;
            }
        }

        $dayNumber = (int) $requestDate->format('w');

        return [
            'day' => $dayNumber,
            'week'  => $currentWeekNumber
        ];
    }


    /**
     * This is helper for retrieve of day name for inline buttons in bot
     * @param $dayNumber integer day number
     * @return string day name
     */

    public function getDayNameByNumber(int $dayNumber)
    {
        $weekDays = ['Понедельник', 'Вторник', 'Среда',
            'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];

        return $weekDays[$dayNumber] ?? null;
    }

    /**
     * @param $groupId string group number
     * @param $day int number of day in week
     * @param $week int number of student's week
     *
     * @return array schedule for group
     * @throws SchedulesNotFoundException
     */

    public function getGroupSchedule(string $groupId, int $day, int $week): array
    {
        $todaySubjects = [];
        $json = $this->request->send("http://students.bsuir.by/api/v1/studentGroup/schedule?studentGroup=$groupId");
        $schedules = json_decode($json);

        if (!$schedules)
            throw new SchedulesNotFoundException('Не удалось получить расписание.');

        foreach ($schedules->schedules[$day - 1]->schedule as $schedule) {
            if (in_array($week, (array) $schedule->weekNumber)) {
                $todaySubjects[] = array(
                    'name' => $schedule->subject,
                    'type' => $schedule->lessonType,
                    'time' => $schedule->lessonTime,
                    'auditory' => $schedule->auditory[0],
                    'subgroup' => $schedule->numSubgroup,
                    'employee' => $schedule->employee[0]->firstName .' '. $schedule->employee[0]->lastName,
                    'note' => $schedules->note ?? null
                );
            }
        }

        return $todaySubjects;
    }

    /**
     * @param string $gid group id to check
     * @return bool
     */
    public function isGroupIsset(string $gid) {
        // bsuir returns status code 200 everytime, this s4ks.
        try {
            $this->getScheduleLastUpdateDate($gid);
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    public function getScheduleLastUpdateDate(string $gid): string
    {
        $response = $this->request->send("https://students.bsuir.by/api/v1/studentGroup/lastUpdateDate?studentGroup=$gid");

        if (!$response)
            throw new \Exception('Group not found');
        $lastUpdateAt = json_decode($response)->{"lastUpdateDate"};

        return $lastUpdateAt;
    }

    /**
     * Parse array with lessons after getting through getGroupSchedule
     * @param $lessons array array with lessons to parse
     * @return string string with message to user
     */
    public function formatSchedulesToReply($lessons)
    {
        $reply = '';
        if (!empty($lessons) && isset($lessons[0]['time'])) {
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

    public function buildInlineKeyboard($day, $week)
    {
        $buttons = [];
        for ($counterDay = 0 ; $counterDay < 7; $counterDay++)
        {
            $bsuirDay = (int) $counterDay + 1;
            $text = ($day == $bsuirDay) ? "🔥" : '';
            $text.= $this->getDayNameByNumber($counterDay);
            $buttons[] = [
                ['text' => $text, 'callback_data' => '/get '. $bsuirDay .' '.$week]
            ];
        }
        return $buttons;
    }

    public function getGroupsList(): array
    {
        $groupsJson = $this->request->send('https://students.bsuir.by/api/v1/groups');

        $groupsList = json_decode($groupsJson, true);

        return $groupsList;
    }
}
