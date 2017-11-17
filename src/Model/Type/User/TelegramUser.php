<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 14.11.17
 * Time: 3:55 PM
 */

namespace BSUIRBot\Model\Type\User;

use BSUIRBot\Model\Type\Type;

class TelegramUser extends Type implements User
{
    /** @var int $id */
    protected $id;
    /** @var boolean $is_bot */
    protected $is_bot;
    /** @var string $first_name */
    protected $first_name;
    /** @var string $last_name */
    protected $last_name;
    /** @var string $username */
    protected $username;
    /** @var string $language_code */
    protected $language_code;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isBot(): bool
    {
        return $this->is_bot;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->language_code;
    }



}