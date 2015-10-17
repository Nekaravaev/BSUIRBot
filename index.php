<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */
date_default_timezone_set('Europe/Kaliningrad');
class BSUIRbot {
    private $prodtoken = ''; // insert your bot token
    public  $request = null;
    public  $user    = null;
    public  $groupID = null;
    public  $message = null;

        /* define an action  */
        function __construct($message){
            $this->request = $message;
            $this->parse($this->request);
        }

        /* route action by command */
       public function parse($command){
            $currentUser = null;
            $this->message = $command;
            $chat = $command->message->chat->id;
            $from = $command->message->from->first_name;
            $username = $command->message->from->username;
            $message = $command->message->text;
            $yes = array('yes', 'Yes', 'Да', 'да', 'Да.', 'да.', 'yes.' ,'Yes.');
            $no  = array('no', 'No', 'Нет', 'нет', 'нет.', 'Нет.', 'no.', 'No.', 'Nope.');

            if ($message == '/today' || $message == '/today@BSUIRBot') {
                $user = $this->getCurrentUser($chat);
                $reply = $this->parseSchedule($this->getGroupSchedule($this->getGroupID($user['group_id'])));
            }

           if ($message == '/tomorrow' || $message == '/tomorrow@BSUIRBot') {
               $week = floor(date("j")/7) + 1;
               $day = date('w');
               $user = $this->getCurrentUser($chat);
               $reply = $this->parseSchedule($this->getGroupSchedule($this->getGroupID($user['group_id']),$day,$week));
           }

           if ($message == '/get' || $message == '/get@BSUIRBot') {
               $reply = 'Немного не так.'.PHP_EOL
                        .'Используй по примеру /get [номер дня недели 1-7] [номер недели [1-4]'.PHP_EOL
                        .'☝ ex: /get 1 4';
           }

           if (preg_match('/^\/get@BSUIRBot [1-7] [1-4]/',$message) || (preg_match('/^\/get [1-7] [1-4]/',$message))) {
               if (preg_match('/^\/get@BSUIRBot [1-7] [1-4]/',$message)) {
                   $day = substr($message, 14);
                   $week = substr($message, 16);
               } else {
                   $day = substr($message, 5);
                   $week = substr($message, 7);
               }
               $day -= 1;
               $user = $this->getCurrentUser($chat);
               $reply = $this->parseSchedule($this->getGroupSchedule($this->getGroupID($user['group_id']),$day,$week));
           }

           if ($message == '/group' || $message == '/group@BSUIRBot') {
               $reply = 'Ошибка!' .PHP_EOL
                        . 'Вы забыли ввести номер группы, ребята.'. PHP_EOL
                        . 'Так: /group@BSUIRBot номер_группы';
           }

            if ($message == '/start' || $message == '/start@BSUIRBot') {
                $user = $this->getCurrentUser($chat);
                if (!$user || $user['status'] == 0) {
                    $reply = "Привет, $from!" . PHP_EOL . "Введи номер группы. 👆";
                    $this->updateUsers('temp', $chat, $username, $from, 1, 0);
                    $this->sendSticker($chat, 'BQADAgADQQADSEvvAQ1q8f_OrLAaAg');
                } else {
                    $user_group = $this->getGroupID($user['group_id']);
                    if ($user_group)
                    {
                       $reply = $this->parseSchedule($this->getGroupSchedule($user_group));
                    }
                    else $reply = 'Не могу найти твою группу.' . PHP_EOL .
                        'Может, все уже закончили, а ты не в курсе?' . PHP_EOL .
                        'Или введи /settings и настройся заново';
                }
            }

            if (is_numeric($message) || preg_match('/^\/group@BSUIRBot [1-9][0-9]{0,15}/',$message)) {
                if (preg_match('/^\/group@BSUIRBot [1-9][0-9]{0,15}/', $message)) {
                    $group = substr($message, 16);
                    $this->updateUsers($group, $chat, $username, $from, 2, 0);
                    $reply = 'Теперь можно получать расписание через /today.';
                } else {
                    $reply = '👍' . PHP_EOL . "Оповещать о расписании по утрам?";
                    $this->updateUsers($message, $chat, $username, $from, 2, 0);
                }
            }

            if (in_array(trim($message),$yes) || in_array(trim($message),$no)){
                $user = $this -> getCurrentUser($chat);
                $cron = (in_array(trim($message),$yes)) ? 1 : 0;
                $reply = '👍' . PHP_EOL . 'Настройки сохранены.' . PHP_EOL;
                $reply .= 'Доступные команды:' . PHP_EOL;
                $reply .= '/today - расписание на сегодня;' . PHP_EOL;
                $reply .= '/get числовой номер дня недели [номер недели] (пример: /get 1 4) - расписание по указанному критерию;' . PHP_EOL;
                $reply .= '/settings - смена группы и настройки крона;';
                $reply .= '/about - рандом инфа.';
                $this->updateUsers($user->group_id, $chat, $username, $from, 3, $cron);
            }

            if ($message == '/about'){
                $reply = 'Запилил Караваев'.PHP_EOL.'Ребят в базе: '.$this->getUsersCounts();
            }
           return $this->sendMessage($chat, $reply);
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

       public function getGroupID($group_name){
           $groups = json_decode(file_get_contents("groups/groups.json"));
           foreach ($groups->studentGroup as $group){
               if ($group->name == $group_name) {
                   $group_id = $group;
                   break;
               }
           }
           return ($group_id) ? $group_id->id : false;
        }

        public function getGroupSchedule($group_id, $dayweek = false, $week = false){
            if (!$week) {
                $week = floor(date("j")/7) + 1;
            }
            $weekDays = array('Воскресенье', 'Понедельник', 'Вторник', 'Среда',
                'Четверг', 'Пятница', 'Суббота');
            if(!$dayweek) {
                $today = $weekDays[date('w')];
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

        public function sendSticker($chat, $sticker){
            $res = array(
                'chat_id' => $chat,
                'sticker' => $sticker
            );
            return $this->sendRequest(true, 'sendSticker', http_build_query($res),$this->prodtoken);
        }

       public function sendMessage($chat, $reply){
            $res = array(
                'chat_id' => $chat,
                'text' => $reply
            );
            return $this->sendRequest(true, 'sendMessage', http_build_query($res),$this->prodtoken);
        }

        protected function updateUsers($group, $userID, $userName, $userDisplayName, $status, $cron){
                $users = json_decode($this->getUsers(), true);
                if (isset($users['groups'][$group])){
                    foreach ($users['groups'] as $singleGroup => $val) {
                        if ($singleGroup == $group) {
                            foreach ($val as $value) {
                                if ($value['user_id'] == $userID) {
                                    $value['group_id'] = $group;
                                    $value['username'] = $userName;
                                    $value['display_name'] = $userDisplayName;
                                    $value['status'] = $status;
                                    $value['cron'] = $cron;
                                    break;
                                } else {
                                    $newUser = array('user_id' => $userID, 'group_id' => $group, 'username' => $userName, 'display_name' => $userDisplayName, 'status' => $status, 'cron' => $cron);
                                    array_push($users['groups'][$group], $newUser);
                                    $users['users'] = (int) $users['users'] + 1;
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $users['groups'][$group] = array();
                    $newUser = array('user_id' => $userID, 'group_id' => $group, 'username' => $userName, 'display_name' => $userDisplayName, 'status' => $status, 'cron' => $cron);
                    array_push($users['groups'][$group], $newUser);
                    $users['users'] = (int) $users['users'] + 1;
                }

                $groups = fopen("groups/users.json", "w");
                $result = fwrite($groups, json_encode($users, JSON_PRETTY_PRINT));
                fclose($groups);
                return $result;
            }

            protected function getUsers(){
                return file_get_contents("groups/users.json");
            }

            protected function getCurrentUser($id){
                $currentUser = false;
                $users = json_decode($this->getUsers(),true);
                foreach ($users['groups'] as $groups){
                    foreach ($groups as $user){
                        if ($user['user_id'] == $id && $user['group_id'] != 'temp'){
                            $currentUser = $user;
                            break 2;
                        }
                    }
                }
                return $currentUser;
            }

            public function getUsersCounts(){
                $json = json_decode($this->getUsers());
                return $json->users;
            }

       public function sendRequest($telegram = true, $method = '', $params = '', $token, $url = '', $assoc=false)
        {
            if ($telegram){
                $ch = curl_init("https://api.telegram.org/bot$token/$method");
            } else
            {
                $ch = curl_init($url);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $response = curl_exec($ch);
            curl_close($ch);
            return json_decode($response,$assoc);
        }
}
    /* Init */
    $telegram = new BSUIRbot(json_decode(file_get_contents('php://input')));

