<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 13.03.17
 * Time: 1:31 PM
 */

namespace BSUIRBot\Controller;
use BSUIRBot\Model\Bot\Bot;
use BSUIRBot\Model\BSUIR;
use BSUIRBot\Model\Type\Type;
use BSUIRBot\Model\User;
use BSUIRBot\Model\Util\CommandParseHelper;
use BSUIRBot\Model\Util\Phrase;

class TelegramController extends Controller
{

    public function __construct(Type $command, Bot $bot, User $user, BSUIR $scheduleInstance, Phrase $phrase, CommandParseHelper $parser)
    {
            $this->bot      = $bot;
            $this->schedule = $scheduleInstance;
            $this->user     = $user;
            $this->groupId  = $user->getGroupId();
            $this->command  = $command;
            $this->parser   = $parser;
            $this->message_type = $this->command->getObjectType();
            $this->phrases = $phrase;
    }

    public function execute() {
        $action = $this->parseMessage();

        if ($this->message_type === 'callback_query'){
            $this->bot->answerCallbackQuery($action->command->callback_query->id, $action->reply);
            $send = $this->bot->editMessageText($this->user->getId(), $this->command->callback_query->getMessage()->getMessageId(), $action->reply, $action->keyboard);
            //$mark = $Controller->bot->editMessageReplyMarkup($action->message->chat_id, $action->message->message_id, $action->keyboard);
        } else
            $send = $this->bot->sendMessage($this->user->getId(), $action->reply, $action->keyboard);


//        $forward = $this->bot->forwardMessage($action->message->chat_id, $action->message->message_id, $action->reply);

        if ($send->ok)
            exit($action->reply);
        else {
//            if (!$send->ok)
//                $reply = $send->description;
//            else
//                $reply = $forward->description;
        }
    }

    public function startAction()
    {
        if ($this->user->getGroupId() == 'temp')
        {
           return [
               'reply' => "Привет, <b>".$this->user->getDisplayName()."</b>!" . PHP_EOL . "Введи номер группы. 👆",
               'keyboard' => ['force_reply' => true]
               ];
        }
        else
            return $this->todayAction();
    }

    public function todayAction()
    {
        date_default_timezone_set("Europe/Minsk");
        $date = $this->schedule->getDayAndWeekByDate(time());
        $schedules = $this->schedule->getGroupSchedule($this->groupId, $date['day'], $date['week']);
        $reply = $this->schedule->formatSchedulesToReply($schedules);

        return [
            'reply' => $reply,
            'keyboard' => []
        ];
    }

    public function scheduleAction()
    {
        $date = $this->schedule->getDayAndWeekByDate(time());
        $schedules = $this->schedule->getGroupSchedule($this->groupId, $date['day'], $date['week']);
        $reply = $this->schedule->formatSchedulesToReply($schedules);

        return [
            'reply' => $reply,
            'keyboard' =>  ['inline_keyboard' => $this->schedule->buildInlineKeyboard($date['day'], $date['week'])]
            ];
    }

    public function getAction($day = '', $week = '')
    {
        if (empty($week) || empty($day))
            return [
                'reply' => 'Немного не так. Введите после /get номер дня недели, а потом еще и номер недели. '. PHP_EOL. 'Формат такой: /get 7 1',
                'keyboard' => []
            ];

        $schedules = $this->schedule->getGroupSchedule($this->groupId, $day, $week);
        $reply = $this->schedule->formatSchedulesToReply($schedules);

        return [
            'reply' => $reply,
            'keyboard' =>  ['inline_keyboard' => $this->schedule->buildInlineKeyboard($day, $week)]
        ];
    }


    public function sendAction($to, $message) {

        $this->bot->sendMessage($to, $message);

        return [
            'reply' => 'Successfully sent',
            'keyboard' =>  []
        ];
    }

    public function tomorrowAction()
    {
        $date = $this->schedule->getDayAndWeekByDate(strtotime('tomorrow'));
        $schedules = $this->schedule->getGroupSchedule($this->groupId, $date['day'], $date['week']);
        $reply = $this->schedule->formatSchedulesToReply($schedules);

        return [
            'reply' => $reply,
            'keyboard' => []
        ];
    }

    public function resetAction()
    {
        $this->user->setStatus($this->user::NEW_USER_STATUS_CODE);
        $this->user->setGroupId('temp');
        $this->user->setCron(false);

        return [
            'reply' => $this->phrases->getPhrase('reset'),
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

    public function dateAction() {
        date_default_timezone_set("Europe/Minsk");
        $date = $this->schedule->getDayAndWeekByDate(time() + 864000);
        return [
            'reply' => $date['week'] . " " .$date['day'],
            'keyboard' => []
        ];
    }

    public function groupAssign($group_id)
    {

        if ($this->schedule->isGroupIsset($group_id)) {
            $this->user->setStatus($this->user::ALMOST_USER_STATUS_CODE);
            $this->user->setGroupId($group_id);

            return [
                'reply' => $this->phrases->getPhrase('groupSaved'),
                'keyboard' => ['force_reply' => true]
            ];

        } else {
            if ($this->logger) {
                $this->logger->notifyError('Group not found', "gid: $group_id");
            }

            return [
                'reply' => $this->phrases->getPhrase('group404'),
                'keyboard' => []
            ];
        }
    }

    public function cronAssign($cron)
    {
        $this->user->setCron($cron);
        $this->user->setStatus($this->user::REGISTERED_USER_STATUS_CODE);
        return [
            'reply' => $this->phrases->getPhrase('settingsSaved'),
            'keyboard' => []
        ];
    }
}