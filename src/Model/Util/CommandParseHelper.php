<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 16.11.17
 * Time: 6:26 PM
 */

namespace BSUIRBot\Model\Util;

class CommandParseHelper
{
    private $yes = ["yes", "Yes", "Да", "да", "Да.", "да.", "yes.", "Yes."];
    private $no  = ["no", "No", "Нет", "нет", "нет.", "Нет.", "no.", "No.", "Nope."];

    public function isYesOrNot($text) {
        return (in_array(trim($text), $this->yes) || in_array(trim($text), $this->no));
    }

    public function isYes($text) {
        return (in_array(trim($text), $this->yes));
    }

    public function isDateAndWeekNumbers($text) {
        return (preg_match('/^\/get [1-7] [1-4]/', $text)) ? true : false;
    }

    public function isNumeric($text) {
        return is_numeric($text);
    }
}