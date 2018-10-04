<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 28.11.16
 * Time: 8:04 PM
 */

namespace BSUIRBot\Model\Bot;

abstract class Bot
{
    protected $token;
    protected $request;
    protected $api;

    /* init, set token  */
    public function __construct($token, $requestClass)
    {
        $this->token = $token;
        $this->request = $requestClass;
    }

    abstract public function sendMessage($chat, $reply, $keyboardLayout = []);

    abstract public function forwardMessage($fromChatId, $messageId, $reply);

    /**
     * Sends request uses Request class
     * @param string $api
     * @param array $params
     *
     * @return \stdClass
     */
    public function sendRequest(string $api = 'telegram', array $params): \stdClass
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

        return json_decode($this->request->send($url, $params));
    }
}
