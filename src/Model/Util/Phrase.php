<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 24.10.16
 * Time: 6:14 AM
 */

namespace BSUIRBot\Model\Util;

use BSUIRBot\Exception\PhraseNotFound;

class Phrase {

	public $phrases;

	public function __construct()
    {
        $this->phrases = [
            "group404" => "Не могу найти твою группу.",
            "command404" => "Команда не распознана.",
            "user404" => "Тут такое дело\nНе могу найти тебя в базе :(\nВведи /start и пройди регистрацию ещё разок.",
            "get404" => "Немного не так.\nИспользуй по примеру /get [номер дня недели 1-7] [номер недели [1-4]\n☝ ex: /get 1 4",
            "groupSaved" => "\nОповещать о расписании по утрам?",
            "settingsSaved" => "\nНастройки сохранены.\nДоступные команды:\n/today - расписание на сегодня\n/get числовой номер дня недели [номер недели] (пример: /get 1 4) - расписание по указанному критерию\n/reset - обновить данные в профиле\n/schedule - получить расписание с пагинацией по дням\n/about - контакты автора",
            "reset" => "Настройки сброшены. Введи номер группы заново."
        ];
    }

    public function getPhrase($phrase){
	    $reply = $this->phrases[$phrase];

	    if (!$reply)
	        throw new PhraseNotFound($phrase);

	    return $reply;
	}

}