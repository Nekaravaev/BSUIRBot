<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 24.10.16
 * Time: 6:14 AM
 */

namespace app\helpers;

class Phrase {

	public static $phrases = [
        "group404" => "Не могу найти твою группу.",
        "command404" => "Команда не распознана.",
        "yes" => ["yes", "Yes", "Да", "да", "Да.", "да.", "yes.", "Yes."],
        "no" => ["no", "No", "Нет", "нет", "нет.", "Нет.", "no.", "No.", "Nope."],
        "user404" => "Тут такое дело\nНе могу найти тебя в базе :(\nВведи /start и пройди регистрацию ещё разок.",
        "get404" => "Немного не так.\nИспользуй по примеру /get [номер дня недели 1-7] [номер недели [1-4]\n☝ ex: /get 1 4",
        "groupSaved" => "\nОповещать о расписании по утрам?",
        "settingsSaved" => "\nНастройки сохранены.\nДоступные команды:\n/today - расписание на сегодня\n/get числовой номер дня недели [номер недели] (пример: /get 1 4) - расписание по указанному критерию\n/about - контакты автора",
        "reset" => "Настройки сброшены. Введи номер группы заново."
    ];


	public static function getPhrase($phrase){
		return self::$phrases[$phrase];
	}

}