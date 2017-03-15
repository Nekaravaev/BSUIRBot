<?php
/**
 * Created by PhpStorm.
 * User: Karavaev
 * Date: 7/25/2015
 * Time: 4:16 PM
 */

namespace app\models\bots;

use app\errors\BreakException;
use app\models\BSUIR;

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
                        'chat_id' => $message->message->chat->id,
                        'type' => $type
                    ];
                    break;
                case 'callback':
                    $return = [
                        'user_id' => $message->callback_query->message->chat->id,
                        'username' => $message->callback_query->message->chat->username,
                        'display_name' => $message->callback_query->message->chat->first_name,
                        'text' => $message->callback_query->data,
                        'message_id' =>  $message->callback_query->message->message_id,
                        'message_raw' => $message,
                        'callback_id' => $message->callback_query->id,
                        'chat_id' => $message->callback_query->message->chat->id,
                        'type' => $type
                    ];
            }
        } else
            throw new BreakException('Не удалось получить сообщение.');

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

    public function editMessageText($chat, $message_id, $reply, $keyboardLayout = [])
    {
        $res = [
            'chat_id' => $chat,
            'message_id' => $message_id,
            'text' => $reply,
            'reply_markup' => json_encode($keyboardLayout)
        ];
        return self::sendRequest('telegram', ['method' => 'editMessageText', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function editMessageReplyMarkup($chat, $message_id, $keyboardLayout)
    {
        $res = [
            'chat_id' => $chat,
            'message_id' => $message_id,
            'reply_markup' => json_encode($keyboardLayout)
        ];
        return self::sendRequest('telegram', ['method' => 'editMessageReplyMarkup', 'params' => http_build_query($res), 'token' => $this->token], false);

    }

    public function forwardMessage($fromChatId, $messageId, $reply)
    {
        $res = [
            'chat_id' => $this->debugchat,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId
        ];
        return self::sendRequest('telegram', ['method' => 'forwardMessage', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function buildInlineKeyboard($day, $week)
    {
        $buttons = [];
        for ($counterDay = $day ; $counterDay < 7; $counterDay++)
        {
            $bsuirDay = (int) $counterDay + 1;
            $buttons[] = [
                ['text' => BSUIR::getDayNameByNumber($counterDay), 'callback_data' => '/get '. $bsuirDay .' '.$week]
            ];
        }
        return $buttons;
    }
}
