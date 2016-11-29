<?php
	/**
	 * Created by PhpStorm.
	 * User: karavaev
	 * Date: 28.11.16
	 * Time: 8:04 PM
	 */

namespace bsuir\app;

class Bot {
	protected $token = '';


	/* init, set token  */
	public function __construct($token)
	{
		$this->token = $token;
	}

	public function returnMessageInfo($message)
	{

	}

	public function sendMessage($chat, $reply)
	{

	}

	public function forwardMessage($from_chat_id, $message_id, $reply)
	{

	}

	public function sendRequest($API = 'telegram', $params, $assoc = false)
	{
		switch ($API) {
			case ( "telegram" ):
				$url = "https://api.telegram.org/bot$this->token/".$params['method'];
				break;
			case ( "VK" ):
				$url = "https://api.vk.com/method/".$params['method'];
				break;
			default:
				$url = $params['url'];
				break;
		}
		$post = $params['params'];
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
		return json_decode($response, $assoc);
	}
}