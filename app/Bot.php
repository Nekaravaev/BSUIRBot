<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 28.11.16
 * Time: 8:04 PM
 */

namespace bsuir\app;

abstract class Bot
{
    protected $token;


    /* init, set token  */
    public function __construct($token)
    {
        $this->token = $token;
    }

    abstract public function returnMessageInfo($message, $type);

    abstract public function sendMessage($curlat, $reply);

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

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params['params']);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, $assoc);
    }
}
