<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 15.11.17
 * Time: 5:22 PM
 */

namespace BSUIRBot\Model\Type\CallbackQuery;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use BSUIRBot\Model\Type\Message\Message;
use BSUIRBot\Model\Type\Type;
use BSUIRBot\Model\Type\User\User;

class TelegramCallbackQuery extends Type
{
    /** @var int $id */
    protected $id;
    /** @var User $from */
    protected $from;
    /** @var Message $message */
    protected $message;
    /** @var  int $inline_message_id */
    protected $inline_message_id;
    /** @var string $chat_instance */
    protected $chat_instance;
    /** @var  string $data */
    protected $data;
    /** @var  string $game_short_name */
    protected $game_short_name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getFrom(): User
    {
        return $this->from;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getInlineMessageId(): int
    {
        return $this->inline_message_id;
    }

    /**
     * @return string
     */
    public function getChatInstance(): string
    {
        return $this->chat_instance;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getGameShortName(): string
    {
        return $this->game_short_name;
    }
}