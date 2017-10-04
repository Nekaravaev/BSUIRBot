<?php
    /**
     * Created by PhpStorm.
     * User: karavaev
     * Date: 28.11.16
     * Time: 5:47 PM
     */

namespace app\models\bots;

use app\Config;
use app\drivers\Redis;

class VK extends Bot
{
    public function __construct($token)
    {
        parent::__construct($token);
    }

    public function returnMessageInfo($message, $type)
    {
        if ($message->type == 'wall_post_new') {
            $userFirstName = ($message->object->from_id > 0) ? $this->getDisplayName($message->object->from_id) : 'Староста от имени группы';
        } else
            $userFirstName =  $this->getDisplayName($message->object->user_id);


        $return = [];
        switch ($type) {
            case 'message_new':
                $return = [
                    'user_id' => $message->object->user_id,
                    'display_name' => $userFirstName,
                    'title' => $message->object->title,
                    'text' => $message->object->body,
                    'type' => $message->type,
                    'message_raw' => $message
                ];
                break;
            case 'message_allow':
                $return = [
                    'user_id' => $message->object->user_id,
                    'display_name' => $userFirstName,
                    'message_raw' => $message,
                    'type' => $message->type
                ];
                break;
            case 'message_deny':
                $return = [
                    'user_id' => $message->object->user_id,
                    'display_name' => $userFirstName,
                    'message_raw' => $message,
                    'type' => $message->type
                ];
                break;
            case 'wall_post_new':
                $return = [
                    'post_id' => $message->object->id,
                    'date' => $message->object->date,
                    'group_id' => $message->group_id,
                    'user_id' => $message->object->from_id,
                    'owner_id' => $message->object->owner_id,
                    'display_name' => $userFirstName,
                    'message_raw' => $message,
                    'type' => $message->type
                ];
                break;
            case 'confirmation':
                $return = [
                    'group_id' => $message->group_id,
                    'reply' => Config::getConfirmationCode(),
                    'type' => $message->type
                ];
                break;
        }

        return $return;
    }

    public function getDisplayName($userId)
    {
        $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.0&lang=ru"));
        $userFirstName = $userInfo->response[0]->first_name.' '.$userInfo->response[0]->last_name;

        return ($userFirstName) ? $userFirstName : null;
    }

    public function sendMessage($chat, $reply, $keyboardLayout = [])
    {
        $res = [
            'message' => $reply,
            'user_id' => $chat,
            'access_token' => $this->token,
            'v' => '5.0'
        ];
        return $this->sendRequest("VK", ['method' => 'messages.send', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function forwardWallPost($chat, $reply, $wallPost)
    {
        $res = [
            'message' => $reply,
            'peer_id' => $chat,
            'attachment' => $wallPost,
            'access_token' => $this->token,
            'v' => '5.60'
        ];
        return $this->sendRequest("VK", ['method' => 'messages.send', 'params' => http_build_query($res), 'token' => $this->token], false);
    }

    public function forwardMessage($fromChatId, $messageId, $reply)
    {
        $res = [
            'message' => $reply,
            'user_id' => $fromChatId,
            'forward_messages' => $messageId,
            'access_token' => $this->token,
            'v' => '5.0'
        ];
        return $this->sendRequest("VK", ['method' => 'messages.send', 'params' => http_build_query($res), 'token' => $this->token], false);
    }
}
