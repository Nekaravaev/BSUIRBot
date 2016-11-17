<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */

class Bot {
    protected $token = '';
    public  $debugchat = -4376451; //747013;

    /* init, set token  */
    function __construct($token){
        $this->token = $token;
    }

    public function returnMessageInfo($message)
    {
        if (!empty($message->message->chat->id)){
            $return = array(
                $message->message->chat->id,
                $message->message->from->username,
                $message->message->from->first_name,
                $message->message->text,
                $message->message->message_id,
                $message
            );
        }
        else
            $return = false;

        return $return;
    }

    public function sendSticker($chat, $sticker)
    {
        $res = array(
            'chat_id' => $chat,
            'sticker' => $sticker
        );
        return $this->sendRequest(true, array('method' => 'sendSticker', 'params' => http_build_query($res), 'token' => $this->token));
    }

    public function sendMessage($chat, $reply)
    {
        $res = array(
            'chat_id' => $chat,
            'text' => $reply
        );
        return $this->sendRequest(true, array('method' => 'sendMessage', 'params' => http_build_query($res), 'token' => $this->token));
    }

    public function forwardMessage($from_chat_id, $message_id , $reply)
    {
        $res = array(
            'chat_id' => $this->debugchat,
            'from_chat_id' => $from_chat_id,
            'message_id' => $message_id,
            'text' => $reply
        );
        return $this->sendRequest(true, array('method' => 'forwardMessage', 'params' => http_build_query($res), 'token' => $this->token));
    }

    public function sendRequest($telegram = true, $params, $assoc = false)
    {
        if ($telegram)
        {
            $url = "https://api.telegram.org/bot$this->token/".$params['method'];
            $post = $params['params'];
        }
        else
        {
            $url  = $params['url'];
            $post = $params['params'];
        }
        return $this->curl($url, $post, $assoc);
    }

    public function curl($url, $params, $assoc = false)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response,$assoc);
    }
}


?>