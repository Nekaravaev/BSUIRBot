<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 16.11.17
 * Time: 4:38 PM
 */

namespace BSUIRBot\Config;
use BSUIRBot\Exception\BreakException;

abstract class Config
{
    public $environment = 'development';

    /*
     * based on user status
     * e.g
     *  "guest" => status 1
     *  "user registered" => status 2
     *  "user fill all needed info" => status 3
     *  "all users" (guest & registered) => status all
     */
    public $permissions = [
        0 => ['/about', '/reset', '/send', '/date'],
        1 => ['numeric', '/start'],
        2 => ['cron'],
        3 => ['/today', '/get', '/tomorrow', '/start', '/schedule']
    ];

    /** @var string $VKtoken */
    protected $VKtoken = '';
    /** @var string $TGtoken */
    protected $TGtoken = '';

    /** @var string $VKConfirmationCode */
    protected $VKConfirmationCode = '';

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return string token
     * @throws BreakException if VK token not filled
     */
    public function getVKtoken():string
    {
        if ($this->VKtoken)
            return $this->VKtoken;

        throw new BreakException('No VK token found');
    }

    /**
     * @return string token
     * @throws BreakException if VK token not filled
     */
    public function getTGtoken():string
    {
        if ($this->TGtoken)
            return $this->TGtoken;

        throw new BreakException('No Telegram token found');
    }

    /**
     * @return string
     * @throws BreakException if VK confirmation code not filled
     */
    public function getVKConfirmationCode(): string
    {
        if ($this->VKConfirmationCode)
            return $this->VKConfirmationCode;

        throw new BreakException('No VK confirmation code found');
    }
}