<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 27.02.17
 * Time: 9:23 PM
 */

namespace app;

use app\errors\BreakException;

class Config
{
    private $VKtoken = '';
    private $TGtoken = '';
    private $TGDebugToken = '';
    private $VKDebugToken = '';
    private $VKConfirmationCode = '';

    private static $instance = null;

    public function __construct(){}

    public function __clone(){}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    /**
     * @return string token
     * @throws BreakException if Telegram Debug token not filled
     */
    public function getTGDebugToken(): string
    {
        if ($this->TGDebugToken)
            return $this->TGDebugToken;
        else
            throw new BreakException('No Telegram Debug token found');
    }

    /**
     * @return string token
     * @throws BreakException if Confirmation token not filled
     */
    public function getConfirmationCode(): string
    {
        if ($this->VKConfirmationCode)
            return $this->VKConfirmationCode;
        else
            throw new BreakException('No VK confirmation code found');
    }

    /**
     * @return string token
     * @throws BreakException if Telegram token not filled
     */
    public function getTGtoken(): string
    {
        if ($this->TGtoken)
            return $this->TGtoken;
        else
            throw new BreakException('No Telegram token found');
    }


    /**
     * @return string token
     * @throws BreakException if VK token not filled
     */
    public function getVKtoken():string
    {
        if ($this->VKtoken)
            return $this->VKtoken;
        else
            throw new BreakException('No VK Token found');
    }

    /**
     * @return string token
     * @throws BreakException if VK Debug token not filled
     */
    public function getVKDebugToken():string
    {
        if ($this->VKDebugToken)
            return $this->VKDebugToken;
        else
            throw new BreakException('No VK Debug token found');
    }
}