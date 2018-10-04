<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 14.11.17
 * Time: 3:56 PM
 */

namespace BSUIRBot\Model\Type\MessageEntity;

use BSUIRBot\Model\Type\Type;
use BSUIRBot\Model\Type\User\User;

class TelegramMessageEntity extends Type implements MessageEntity {
    /** @var string $type */
    protected $type;

    /** @var int $offset */
    protected $offset;

    /** @var int $length */
    protected $length;

    /** @var string $url */
    protected $url;

    /** @var User $user */
    protected $user;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

}