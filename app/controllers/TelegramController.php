<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 13.03.17
 * Time: 1:31 PM
 */

namespace app\controllers;
use app\drivers\Redis;
use app\helpers\Phrase;
use app\models\bots\Telegram;
use app\Config;
use app\models\BSUIR;

class TelegramController
{
    /**
     * @var $bot Telegram class
     */
    public $bot;

    /**
     * @var $debugBot Telegram class
     */
    public $debugBot;

    /**
     * @var $message object json from telegram
     */
    public $message;

    /**
     * @var $availableMethods array with available user's methods by status (keys - statuses)
     */
    public $availableMethods = [
        '1' => ['numeric', '/start'],
        '2' => ['cron'],
        '3' => ['/today', '/get', '/tomorrow', '/start', '/schedule'],
        'all' => ['/about', '/reset']
    ];

    /**
     * @var $user object of user
     */

    public $user;

    /**
     * @var $Redis Redis
     */
    public $Redis;

    /**
     * @var $groupId string
     */
    public $groupId = 'temp';

    public function __construct($message)
    {
        try {
            $this->bot      = new Telegram(Config::getTGtoken());
            $this->debugBot = new Telegram(Config::getTGDebugToken());
            $this->Redis    = new Redis();
            $this->message  = (object) $this->bot->returnMessageInfo($message, (!empty($message->callback_query)) ? 'callback' : 'message');
            $user = $this->Redis->getCurrentUser("user:".$this->message->user_id);
            if (empty($user))
            {
                $this->user = (object) $this->Redis->manageUser($this->message->user_id, [
                    'gid' => 'temp',
                    'username' => $this->message->username,
                    'display_name' => $this->message->display_name,
                    'status' => 1,
                    'cron' => 1
                ]);
            } else
            {
                $this->user = (object) $user;
                if ($this->user->status > 2)
                {
                    if (BSUIR::getGroupID($this->user->group_id))
                    {
                        $this->groupId = BSUIR::getGroupID($this->user->group_id);
                    } else {
                        $this->user = (object) $this->Redis->manageUser($this->message->user_id, [
                            'gid' => 'temp',
                            'username' => $this->message->username,
                            'display_name' => $this->message->display_name,
                            'status' => 1,
                            'cron' => 1
                        ]);
                    }

                }
            }
        } catch (\ErrorException $errorException) {
            throw $errorException;
        } catch (\Exception $e) {
            throw $e;
        } catch (\Error $error) {
            throw $error;
        }
    }

    public function checkPermissions()
    {
        $text = $this->message->text;
        $methods = null;
        $type = null;
        switch ($text) {
            case is_numeric($text):
                $type = 'numeric';
                break;
            case (in_array(trim($text), Phrase::getPhrase('yes')) || in_array(trim($text), Phrase::getPhrase('no'))):
                $type = 'cron';
                break;
            case (preg_match('/^\/get [1-7] [1-4]/', $text) ? true : false):
                $type = '/get';
                break;
            default:
                $type = $text;
                break;
        }

        return (in_array($type, $this->availableMethods[$this->user->status]) || in_array($type, $this->availableMethods['all'])) ? true : false;
    }

    public function parseMessage()
    {
        if (!$this->checkPermissions())
            throw new \Error('Нет доступа к данной функции на этом этапе.');

        $reply = [
            'reply' => 'Команда не найдена.',
            'keyboard' => []
        ];
        $action = 'noAction';

        preg_match("/[\w]+/", $this->message->text, $matches);
        if (!empty($matches[0])){
            $action = $matches[0];
            $getQuery = preg_replace("/\/$action/", "$2 $1", $this->message->text);
        }


        if (!empty($getQuery))
            $params = explode(' ', trim($getQuery));

        if (method_exists($this, $action.'Action')){

            if (!empty($params) && count($params) == 2)
            {
                list($argument1, $argument2) = $params;
                 $reply = $this->{$action.'Action'}($argument1, $argument2);
            } else
                $reply = $this->{$action.'Action'}();
        } else {

            if (is_numeric($this->message->text))
                $reply = $this->groupAssign($this->message->text);

            if ((in_array(trim($this->message->text), Phrase::getPhrase('yes')) || in_array(trim($this->message->text), Phrase::getPhrase('no'))))
                $reply = $this->cronAssign((in_array(trim($this->message->text), Phrase::getPhrase('yes'))) ? "1" : "0");
        }

        return (object) [
            'chat' => $this->message->user_id,
            'reply' => $reply['reply'],
            'keyboard' => $reply['keyboard'],
            'message' => $this->message
        ];

    }

    public function startAction()
    {
        if ($this->user->group_id == 'temp')
        {
           return [
               'reply' => "Привет, <b>".$this->user->display_name."</b>!" . PHP_EOL . "Введи номер группы. 👆",
               'keyboard' => ['force_reply' => true]
               ];
        }
        else
            return $this->todayAction();
    }

    public function todayAction()
    {
        $date = BSUIR::getDate(time());

        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $date['day'], $date['week'])),
            'keyboard' => []
        ];
    }

    public function scheduleAction()
    {
        $date = BSUIR::getDate(time());
        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $date['day'], $date['week'])),
            'keyboard' =>  ['inline_keyboard' => $this->bot->buildInlineKeyboard($date['day'], $date['week'])]
            ];
    }

    public function getAction($day = '', $week = '')
    {
        if (empty($week) || empty($day))
            return [
                'reply' => 'Немного не так. Введите после /get номер дня недели, а потом еще и номер недели. '. PHP_EOL. 'Формат такой: /get 7 1',
                'keyboard' => []
            ];

        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $day, $week)),
            'keyboard' =>  ['inline_keyboard' => $this->bot->buildInlineKeyboard($day, $week)]
        ];
    }

    public function tomorrowAction()
    {
        $date = BSUIR::getDate(strtotime('tomorrow'));
        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $date['day'], $date['week'])),
            'keyboard' => []
        ];
    }

    public function resetAction()
    {
        $this->Redis->manageUser($this->message->user_id, [
            'gid' => 'temp',
            'username' => $this->message->username,
            'display_name' => $this->message->display_name,
            'status' => 1,
            'cron' => 1
        ]);

        return [
            'reply' => Phrase::getPhrase('reset'),
            'keyboard' => ['force_reply' => true]
        ];
    }

    public function aboutAction()
    {
        return [
            'reply' => 'Запилил Андрей М. ( @Karavay )' . PHP_EOL . 'Пользователей: <strong>' . $this->Redis->getUsersCount().'</strong>',
            'keyboard' => []
        ];
    }

    public function groupAssign($group)
    {
        if (BSUIR::getGroupID($group))
            $this->Redis->manageUser($this->message->user_id, [
                'gid' => $group,
                'username' => $this->message->username,
                'display_name' => $this->message->display_name,
                'status' => 2,
                'cron' => 1
            ]);

        return [
            'reply' => Phrase::getPhrase('groupSaved'),
            'keyboard' => ['force_reply' => true]
        ];
    }

    public function cronAssign($cron)
    {
        $this->Redis->manageUser($this->message->user_id, [
            'gid' => $this->user->group_id,
            'username' => $this->message->username,
            'display_name' => $this->message->display_name,
            'status' => 3,
            'cron' => $cron
        ]);

        return [
            'reply' => Phrase::getPhrase('settingsSaved'),
            'keyboard' => []
        ];
    }
}