<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 13.03.17
 * Time: 1:31 PM
 */

namespace BSUIRBot\Controller;
use app\drivers\Redis;
use app\helpers\Phrase;
use app\models\bots\VK;
use app\Config;
use app\models\BSUIR;

class VKController extends Controller
{
    /**
     * @var $bot VK class
     */
    public $bot;

    /**
     * @var $message object json from telegram
     */
    public $message;

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
    public $groupId = '21433';

    public function __construct($message)
    {
        try {
            $this->bot      = new VK(Config::getVKtoken());
            $this->Redis    = new Redis();
            $this->message  = (object) $this->bot->returnMessageInfo($message, (!empty($message->type)) ? $message->type : '');

        } catch (\ErrorException $errorException) {
            throw $errorException;
        } catch (\Exception $e) {
            throw $e;
        } catch (\Error $error) {
            throw $error;
        }
    }

    public function messageNewAction()
    {
        $reply = [
            'reply' => 'Команда не найдена. Лист доступных команд: '.PHP_EOL.'/today '. PHP_EOL . ' /tomorrow '. PHP_EOL . '/about',
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
        }

        return (object) [
            'chat' => $this->message->user_id,
            'reply' => $reply['reply'],
            'keyboard' => $reply['keyboard'],
            'message' => $this->message
        ];
    }

    public function wallPostNewAction()
    {
       $post = $this->Redis->getLatestVKPost();
       if ($post) {
            if (($this->message->date - (int) $post->date) < 60)
            {
                $this->Redis->setLatestVKPost($this->message);
                exit();
            }
       }
        $post = $this->Redis->setLatestVKPost($this->message);
        $variations = ['сообщает', 'говорит', 'глаголит', 'молвит'];
        $reply = $this->message->display_name. ' '.$variations[rand(0, count($variations) - 1)].':';

        $notificationUsers = $this->Redis->getFollowersVKUpdates();

        foreach ($notificationUsers as $key => $userRedis) {
            $user = (object) $this->Redis->getCurrentUser("$userRedis");
            if (isset($user->user_id))
                $this->bot->forwardWallPost($user->user_id, $reply, 'wall-'.$this->message->group_id.'_'.$this->message->post_id);
        }

       return 'ok';
    }

    public function messageAllowAction(){

        $this->Redis->addToUpdatesVKGroup($this->message->user_id, [
            'user_id' => $this->message->user_id,
            'display_name' => $this->message->display_name
        ]);

        $reply = $this->message->display_name . ', ты подписан на обновления стены.';

        return $this->bot->sendMessage($this->message->user_id, $reply);
    }

    public function messageDenyAction()
    {
        $remove =  $this->Redis->removeFromUpdatesVKGroup($this->message->user_id);
        $reply = $this->message->display_name . ', ты выпилен из списка получения обновлений стены.';

        return $this->bot->sendMessage($this->message->user_id, $reply);
    }

    public function parseMessage()
    {
        switch ($this->message->type){
            case 'wall_post_new':
                $reply = $this->wallPostNewAction();
                break;
            case 'message_new':
                $reply = $this->messageNewAction();
                break;
            case 'message_allow':
                $reply = $this->messageAllowAction();
                break;
            case 'message_deny':
                $reply = $this->messageDenyAction();
                break;
            case 'confirmation':
                $reply = $this->confirmationAction();
                break;
        }

        return $reply;
    }



    public function todayAction()
    {
       $date = BSUIR::getDayAndWeekByDate(time());

        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $date['day'], $date['week'])),
            'keyboard' => []
        ];
    }

    public function postAction()
    {
        $post = $this->Redis->getLatestVKPost();

        return [
            'reply' => json_encode($post),
            'keyboard' => []
        ];
    }

    public function confirmationAction()
    {
        return $this->message->reply;
    }

    public function scheduleAction()
    {
        $date = BSUIR::getDayAndWeekByDate(time());
        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $date['day'], $date['week'])),
            'keyboard' =>  []
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
            'keyboard' =>  []
        ];
    }

    public function tomorrowAction()
    {
        $date = BSUIR::getDayAndWeekByDate(strtotime('tomorrow'));
        return [
            'reply' => BSUIR::parseSchedule(BSUIR::getGroupSchedule($this->groupId, $date['day'], $date['week'])),
            'keyboard' => []
        ];
    }

    public function aboutAction()
    {
        return [
            'reply' => 'Запилил Андрей М. ( @Karavay )' . PHP_EOL . 'Пользователей: <strong>' . $this->Redis->getUsersCount().'</strong>',
            'keyboard' => []
        ];
    }
}