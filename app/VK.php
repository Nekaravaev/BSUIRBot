<?php
    /**
     * Created by PhpStorm.
     * User: karavaev
     * Date: 28.11.16
     * Time: 5:47 PM
     */

namespace bsuir\app;


class VK extends Bot
{
    public function __construct($token)
    {
        parent::__construct($token);
    }

    public function returnMessageInfo($message, $type)
    {
        $userFirstName = ($message->object->from_id > 0) ? $this->getFirstname($message->object->user_id) : 'группы';

        $return = [];
        switch ($type) {
            case 'message_new':
                $return = [
                    $message->object->user_id,
                    $userFirstName,
                    $message->object->title,
                    $message->object->body,
                    $message->type,
                    $message
                ];
                break;
            case 'message_allow':
                $return = [
                    $message->object->user_id,
                    $userFirstName,
                    $message
                ];
                break;
            case 'message_deny':
                $return = [
                    $message->object->user_id,
                    $userFirstName,
                    $message
                ];
                break;
            case 'wall_post_new':
                $return = [
                    $message->object->id,
                    $message->group_id,
                    $message->object->from_id,
                    $message->object->owner_id,
                    $userFirstName,
                    $message
                ];
                break;
        }

        return $return;
    }

    public function getFirstname($userId)
    {
        $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&v=5.0"));
        $userFirstName = $userInfo->response[0]->first_name;

        return ($userFirstName) ? $userFirstName : null;
    }

    public function sendMessage($chat, $reply)
    {
        $res = [
            'message' => $reply,
            'user_id' => $chat,
            'access_token' => $this->token,
            'v' => '5.0'
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
