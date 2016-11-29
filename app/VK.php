<?php
	/**
	 * Created by PhpStorm.
	 * User: karavaev
	 * Date: 28.11.16
	 * Time: 5:47 PM
	 */

namespace bsuir\app;
use bsuir\app\Bot;

class VK extends Bot
{

	public function __construct($token)
	{
		parent::__construct($token);
	}

	public function returnMessageInfo($message)
	{
		if (!empty($message->object->id)) {
			$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$message->object->user_id}&v=5.0"));

			$userFirstName = $userInfo->response[0]->first_name;

			$return = [
				$message->object->user_id,
				$userFirstName,
				$message->object->title,
				$message->object->body,
				$message->type,
				$message
			];
		} else {
			$return = false;
		}

		return $return;
	}

	public function sendMessage($chat, $reply)
	{
		$res = [
			'message' => $reply,
			'user_id' => $chat,
			'access_token' => $this->token,
			'v' => '5.0'
		];
		return $this->sendRequest("VK", ['method' => 'messages.send', 'params' => http_build_query($res), 'token' => $this->token]);
	}
}
