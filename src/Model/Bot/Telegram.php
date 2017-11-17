<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */

namespace BSUIRBot\Model\Bot;


class Telegram extends Bot
{
    protected $api = 'telegram';

    public function sendSticker($chat, $sticker)
    {
        $res = [
            'chat_id' => $chat,
            'sticker' => $sticker
        ];
        return $this->sendRequest($this->api,
            ['method' => 'sendSticker', 'params' => http_build_query($res), 'token' => $this->token]);
    }

    public function sendMessage($chat, $reply, $keyboardLayout = [])
    {
        $res = [
            'chat_id' => $chat,
            'text' => $reply,
            'parse_mode' => 'HTML',
            'reply_markup' => (!empty($keyboardLayout)) ? json_encode($keyboardLayout) : ''
        ];
        return $this->sendRequest($this->api,
            ['method' => 'sendMessage', 'params' => http_build_query($res), 'token' => $this->token]);
    }

    public function answerCallbackQuery($callbackQueryId, $reply)
    {
        $res = [
            'callback_query_id' => $callbackQueryId,
            'text' => $reply,
            'show_alert' => false
        ];
        return $this->sendRequest($this->api,
            ['method' => 'answerCallbackQuery', 'params' => http_build_query($res), 'token' => $this->token]);
    }

    public function editMessageText($chat, $message_id, $reply, $keyboardLayout = [])
    {
        $res = [
            'chat_id' => $chat,
            'message_id' => $message_id,
            'text' => $reply,
            'reply_markup' => json_encode($keyboardLayout)
        ];
        return $this->sendRequest($this->api,
            ['method' => 'editMessageText', 'params' => http_build_query($res), 'token' => $this->token]);
    }

    public function editMessageReplyMarkup($chat, $message_id, $keyboardLayout)
    {
        $res = [
            'chat_id' => $chat,
            'message_id' => $message_id,
            'reply_markup' => json_encode($keyboardLayout)
        ];
        return $this->sendRequest($this->api,
            ['method' => 'editMessageReplyMarkup', 'params' => http_build_query($res), 'token' => $this->token]);

    }

    public function forwardMessage($fromChatId, $messageId, $reply)
    {
        $res = [
            'chat_id' => $this->debugchat,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId
        ];
        return $this->sendRequest($this->api,
            ['method' => 'forwardMessage', 'params' => http_build_query($res), 'token' => $this->token]);
    }

    public function getUsername() {
        $response = $this->sendRequest($this->api, ['method' => 'getMe', 'token' => $this->token]);

        return $response->result->username;
    }
}
