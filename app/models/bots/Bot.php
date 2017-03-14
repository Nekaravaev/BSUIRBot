<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 28.11.16
 * Time: 8:04 PM
 */

namespace app\models\bots;

use app\helpers\Curl;

abstract class Bot
{
    protected $token;


    /* init, set token  */
    public function __construct($token)
    {
        $this->token = $token;
    }

    abstract public function returnMessageInfo($message, $type);

    abstract public function sendMessage($chat, $reply);

    abstract public function forwardMessage($fromChatId, $messageId, $reply);

    public function sendRequest($api = 'telegram', $params, $assoc)
    {
        switch ($api) {
            case ('telegram'):
                $url = 'https://api.telegram.org/bot' . $this->token . '/' .$params['method'];
                break;
            case ('VK'):
                $url = 'https://api.vk.com/method/'.$params['method'];
                break;
            default:
                $url = $params['url'];
                break;
        }

        return json_decode(Curl::getData($url, $params), $assoc);
    }
}
