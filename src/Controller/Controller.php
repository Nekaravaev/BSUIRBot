<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 16.11.17
 * Time: 3:07 PM
 */

namespace BSUIRBot\Controller;

use BSUIRBot\Model\Bot\Bot;
use BSUIRBot\Model\BSUIR;
use BSUIRBot\Model\Database\Redis;
use BSUIRBot\Model\Type\Type;
use BSUIRBot\Model\User;
use BSUIRBot\Model\Util\CommandParseHelper;
use BSUIRBot\Model\Util\CommandParser;
use BSUIRBot\Model\Util\Phrase;

class Controller
{
    /**
     * @var Bot $bot Telegram class
     */
    protected $bot;

    /**
     * @var Type $message object json from telegram
     */
    public $command;

    /**
     * @var User $user object of user
     */

    public $user;

    /**
     * @var Redis $db Redis
     */
    public $db;

    /**
     * @var array $commandList available command list
     */
    protected $commandList;

    /**
     * @var Phrase $phrases Phrases instance
     */
    protected $phrases;

    /**
     * @var int|string $groupId string
     */
    public $groupId = 'temp';

    /**
     * @var CommandParseHelper $parser
     */
    protected $parser;

    /** @var BSUIR $schedule */
    protected $schedule;

    /** @var string $message_type */
    protected $message_type = 'message';

    /** @var \Bugsnag\Client; */
    protected $logger;

    public function parseMessage()
    {
        $botUsername = $this->bot->getUsername();
        $text = ($this->message_type === 'callback_query') ? $this->command->{$this->message_type}->getData() : $this->command->{$this->message_type}->getText();
        $text = str_replace("@{$botUsername}", '', $text);

        if (!$this->user->checkPermissions($text))
            throw new \Error('Нет доступа к данной функции на этом этапе.');

        $reply = [
            'reply' => 'Команда не найдена.',
            'keyboard' => []
        ];
        $action = 'noAction';

        preg_match("/[\w]+/", $text, $matches);
        if (!empty($matches[0])){
            $action = $matches[0];
            $getQuery = preg_replace("/\/$action/", "$2 $1", $text);
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

            if (is_numeric($text))
                $reply = $this->groupAssign($text);

            if ($this->parser->isYesOrNot($text))
                $reply = $this->cronAssign($this->parser->isYes($text));
        }

        return (object) [
            'chat' => $this->user->getId(),
            'reply' => $reply['reply'],
            'keyboard' => $reply['keyboard'],
            'message' => $this->command
        ];
    }

    public function setLogger(\Bugsnag\Client $logger) {
        $this->logger = $logger;
    }
}