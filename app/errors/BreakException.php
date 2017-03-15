<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 15.03.17
 * Time: 10:47 AM
 */

namespace app\errors;


class BreakException extends \Exception
{
    public function returnMessage()
    {
        return 'Произошла ошибка, бот не может работать дальше. <br/> Информация: '.$this->getMessage();
    }
}