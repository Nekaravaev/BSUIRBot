<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */

namespace bsuir\app;

class Telegram extends Bot
{
    public $debugchat = -4376451; //747013;

    public function returnMessageInfo($message, $type)
    {
        $return = [];
        if (!empty($message->message->chat->id) && $type) {
            $return = [
                $message->message->chat->id,
                $message->message->from->username,
                $message->message->from->first_name,
                $message->message->text,
                $message->message->message_id,
                $message
            ];
        }
        return $return;
    }

    public function sendSticker($chat, $sticker)
    {
        $res = [
            'chat_id' => $chat,
            'sticker' => $sticker
        ];
        return self::sendRequest('telegram', ['method' => 'sendSticker', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function sendMessage($chat, $reply)
    {
        $res = [
            'chat_id' => $chat,
            'text' => $reply
        ];
        return self::sendRequest('telegram', ['method' => 'sendMessage', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function forwardMessage($fromChatId, $messageId, $reply)
    {
        $res = [
            'chat_id' => $this->debugchat,
            'from_chat_id' => $fromChatId,
            'messageId' => $messageId,
            'text' => $reply
        ];
        return self::sendRequest('telegram', ['method' => 'forwardMessage', 'params' => http_build_query($res), 'token' => $this->token], false);
    }
}
