<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 13.10.17
 * Time: 3:51 PM
 */

namespace BSUIRBot\Model;

use BSUIRBot\Exception\InvalidInputException;
use BSUIRBot\Model\Database\Redis;
use BSUIRBot\Model\Type\Chat\Chat;
use BSUIRBot\Model\Type\Chat\TelegramChat;
use BSUIRBot\Model\Util\CommandParseHelper;

class User {

    const NEW_USER_STATUS_CODE = 1;
    const ALMOST_USER_STATUS_CODE = 2;
    const REGISTERED_USER_STATUS_CODE = 3;
    const ALL_USERS_STATUS_CODE = 0;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $first_name;

    /**
     * @var string
     */
    protected $last_name;

    /**
     * @var int
     */
    protected $group_id = 0;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $display_name;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var bool
     */
    protected $cron = false;

    /** @var string $type */
    protected $type;

    /**
     * @var Redis $db
     */
    private $db;

    /**
     * @var array $commandList
     */
    private $commandList;

    /**
     * @var \Bugsnag\Client $logger;
     */
    private $logger;

    /**
     * @var CommandParseHelper $parser
     */

    private $parser;

    /**
     * User constructor.
     * Fills object from database
     *
     * @param Redis $database
     * @param array $commandList array with status => available commands
     * @param CommandParseHelper $parser
     *
     * @internal param $CommandPar
     *
     */
    public function __construct(Redis $database, array $commandList, CommandParseHelper $parser)
    {
        $this->db = $database;
        $this->commandList = $commandList;
        $this->parser = $parser;
    }

    public function setLogger($logger) {
        $this->logger = $logger;
    }

    public function attributes():array
    {
        $attributes = [];
        $class = new \ReflectionClass($this);
        foreach ($class->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            if (!$property->isStatic()) {
                $property->setAccessible(true);
                $attributes[$property->getName()] = $property->getValue($this);
                $property->setAccessible(false);
            }
        }
        return $attributes;
    }

    public function checkPermissions($text)
    {
        $commands = $this->getAvailableCommands();
        $type = null;

        switch ($text) {
            case ($this->parser->isNumeric($text)):
                $type = 'numeric';
                break;
            case ($this->parser->isYesOrNot($text)):
                $type = 'cron';
                break;
            case ($this->parser->isDateAndWeekNumbers($text)):
                $type = '/get';
                break;
            default:
                $type = $text;
                break;
        }
        return (in_array($type, $commands));
    }

    /**
     * @param TelegramChat $chat
     * fill an instance
     *
     * @return User
     * @throws \Exception
     */
    public function load(TelegramChat $chat): User {
        $values = $this->db->getUser($chat->getId());
        $attributes = $this->attributes();

        if (!isset($values->type)) {
            if (!empty($this->logger)) {
                $message_from = ($chat->getTitle()) ? : ($chat->getUsername()) ? "@{$chat->getUsername()}" : $chat->getFirstName();
                $this->logger->notifyError('user not found', 'register new '. $chat->getType() .' ' . $message_from);
            }

            foreach ($attributes as $attribute => $val) {
                if ($value = $chat->getAttributeValue($attribute))
                    $this->$attribute = $value;
            }
            $this->setDisplayName($chat->getType() == 'group' ? $chat->getTitle() : $chat->getFirstName());
            $this->setId($chat->getId());
            $this->setStatus(self::NEW_USER_STATUS_CODE);
            $this->setCron(false);
            $this->save();
        }

        foreach ($values as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    public function setId(int $user_id)
    {
        $this->id = $user_id;
    }

    /**
     * @param int $group_id
     */
    public function setGroupId(int $group_id)
    {
        $this->group_id = $group_id;
        $this->save();
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @param string $display_name
     */
    public function setDisplayName(string $display_name)
    {
        $this->display_name = $display_name;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * @param bool $cron
     */
    public function setCron(bool $cron)
    {
        $this->cron = $cron;
        $this->save();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /*
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isCron()
    {
        return $this->cron;
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
     * Saves model to database
     */
    public function save()
    {
        return $this->db->updateUser($this);
    }

    public function getAvailableCommands(): array
    {
        return array_merge($this->commandList[$this->status], $this->commandList[static::ALL_USERS_STATUS_CODE]);
    }

    public function getUsersCount(): int
    {
        return $this->db->getUsersCount();
    }


}