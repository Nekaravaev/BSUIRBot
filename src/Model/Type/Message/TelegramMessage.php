<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 14.11.17
 * Time: 3:46 PM
 */

namespace BSUIRBot\Model\Type\Message;

use BSUIRBot\Model\Type\Chat\Chat;
use BSUIRBot\Model\Type\MessageEntity\MessageEntity;
use BSUIRBot\Model\Type\Type;
use BSUIRBot\Model\Type\User\User;

class TelegramMessage extends Type implements Message {
    /** @var int $id */
    protected $message_id;
    /** @var User $from */
    protected $from;
    /** @var int $date  */
    protected $date;
    /** @var string $text */
    protected $text;
    /** @var Chat $chat */
    protected $chat;

    /** @var User $forward_from */
    protected $forward_from;
    /** @var int $forward_from_message_id  */
    protected $forward_from_message_id;
    /** @var string $forward_signature */
    protected $forward_signature;
    /** @var integer $forward_date */
    protected $forward_date;
    /** @var integer $edit_date */
    protected $edit_date;
    /** @var string $author_signature */
    protected $author_signature;
    /** @var MessageEntity[] $entities */
    protected $entities;

    public function __construct(\stdClass $input) {
//        if (!$this->validateMessage($input))
//            throw new InvalidInputException('Комманда не валидна.');

    }


    public function validateMessage($input):bool
    {
        return (isset($input->message_id));
    }

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->message_id;
    }

    /**
     * @return int
     */
    public function getUpdateId(): int
    {
        return $this->update_id;
    }

    /**
     * @return User
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return int
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return Chat
     */
    public function getChat(): Chat
    {
        return $this->chat;
    }

    /**
     * @return User
     */
    public function getForwardFrom()
    {
        return $this->forward_from;
    }

    /**
     * @return int
     */
    public function getForwardFromMessageId()
    {
        return $this->forward_from_message_id;
    }

    /**
     * @return string
     */
    public function getForwardSignature()
    {
        return $this->forward_signature;
    }

    /**
     * @return int
     */
    public function getForwardDate()
    {
        return $this->forward_date;
    }

    /**
     * @return int
     */
    public function getEditDate()
    {
        return $this->edit_date;
    }

    /**
     * @return string
     */
    public function getAuthorSignature()
    {
        return $this->author_signature;
    }

    /**
     * @return MessageEntity[]
     */
    public function getEntities()
    {
        return $this->entities;
    }


}