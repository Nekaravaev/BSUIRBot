<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 14.11.17
 * Time: 3:56 PM
 */

namespace BSUIRBot\Model\Type\Chat;

use BSUIRBot\Model\Type\Type;

class TelegramChat extends Type implements Chat
{

    /** @var int $id */
    protected $id;
    /** @var string $type */
    protected $type;
    /** @var string $title */
    protected $title;
    /** @var string $username */
    protected $username;
    /** @var string $first_name */
    protected $first_name;
    /** @var string $last_name */
    protected $last_name;
    /** @var  boolean $all_members_are_administrators */
    protected $all_members_are_administrators;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * @return bool
     */
    public function isAllMembersAreAdministrators()
    {
        return $this->all_members_are_administrators;
    }
}