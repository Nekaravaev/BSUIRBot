<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */

namespace app\models\bots;

class Telegram extends Bot
{
    public $debugchat = -4376451; //747013;

    public function returnMessageInfo($message, $type)
    {
        $return = [];
        if (!empty($type)) {
            switch ($type) {
                case 'message':
                    $return = [
                       'user_id' => $message->message->chat->id,
                        'username' => $message->message->from->username,
                        'display_name' => $message->message->from->first_name,
                        'text' => $message->message->text,
                        'message_id' => $message->message->message_id,
                        'message_raw' => $message,
                        'type' => $type
                    ];
                    break;
                case 'callback':
                    $return = [
                        'user_id' => $message->callback_query->message->from->id,
                        'username' => $message->callback_query->message->chat->username,
                        'display_name' => $message->callback_query->message->chat->first_name,
                        'text' => $message->callback_query->data,
                        'message_id' =>  $message->callback_query->message->message_id,
                        'message_raw' => $message,
                        'callback_id' => $message->callback_query->id,
                        'type' => $type
                    ];
            }

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

    public function sendMessage($chat, $reply, $keyboardLayout = [])
    {
        $res = [
            'chat_id' => $chat,
            'text' => $reply,
            'parse_mode' => 'HTML',
            'reply_markup' => (!empty($keyboardLayout)) ? json_encode($keyboardLayout) : ''
        ];
        return self::sendRequest('telegram', ['method' => 'sendMessage', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function answerCallbackQuery($callbackQueryId, $reply)
    {
        $res = [
            'callback_query_id' => $callbackQueryId,
            'text' => $reply,
            'show_alert' => false
        ];
        return self::sendRequest('telegram', ['method' => 'answerCallbackQuery', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function editMessageReplyMarkup($chat, $message_id, $keyboardLayout)
    {
        $res = [
            'chat_id' => $chat,
            'message_id' => $message_id,
            'reply_markup' => $keyboardLayout
        ];
        return self::sendRequest('telegram', ['method' => 'editMessageReplyMarkup', 'params' => http_build_query($res), 'token' => $this->token], false);

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
