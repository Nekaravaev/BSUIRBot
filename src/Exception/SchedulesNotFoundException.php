<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 15.03.17
 * Time: 10:47 AM
 */

namespace BSUIRBot\Exception;


class SchedulesNotFoundException extends \Exception
{
    /**
     * @return string
     */
    public function returnMessage(): string
    {
        return 'Произошла ошибка, бот не может работать дальше. <br/> Информация: '.$this->getMessage();
    }
}