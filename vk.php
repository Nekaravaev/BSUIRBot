<?php
	/**
	 * Created by PhpStorm.
	 * User: Karavaev
	 * Date: 7/25/2015
	 * Time: 4:16 PM
	 */
	date_default_timezone_set("Europe/Minsk");
	require __DIR__ . '/vendor/autoload.php';
	use bsuir\app\VK as Bot;
	use bsuir\app\BSUIR;
	use bsuir\drivers\Redis as User;
	use bsuir\helpers\Phrases;

// init
	$config   = json_decode(file_get_contents('info/config.json'));
	$token    = $config->vk->token;

	$bot      = new Bot($token);

	list( $chat, $name, $title, $message, $messageType, $message_raw ) = $bot->returnMessageInfo( json_decode( file_get_contents( 'php://input' ) ) );

	$user  = new User('info');
	$schedule = new BSUIR();
	$phrase = new Phrases();

	$reply = $phrase::getPhrase('command404');
	$groupId = $schedule->getGroupID("581062");

//act by message

	if ($message == '/today') {
		$date = $schedule->getDate();
		$reply = $schedule->parseSchedule($schedule->getGroupSchedule($groupId, $date['day'], $date['week']));
	}

	if ($message == '/date') {
		$date = $schedule->getDate();
		$reply = "Сегодня ".$date['day']." день".PHP_EOL.$date['week']." недели".PHP_EOL;
	}

	if ($message == '/tomorrow') {
		$date = $schedule->getDate(true);
		$reply = $schedule->parseSchedule($schedule->getGroupSchedule($groupId, $date['day'], $date['week']));
	}

	if ($message == '/get') {
		$reply = $phrase::getPhrase('get404');
	}

	if (preg_match('/^\/get [1-7] [1-4]/', $message)) {
		$day  = substr($message, 5, 1);
		$week = substr($message, 7, 1);
		$reply = $schedule->parseSchedule($schedule->getGroupSchedule($groupId, $day, $week));
	}

	if ($message == '/about') {
		$reply = 'Запилил Андрей М. (@Karavay)';
	}

// end act by message

	$bot->sendMessage($chat, $reply);

	//return ok to callback API
	echo "ok";
