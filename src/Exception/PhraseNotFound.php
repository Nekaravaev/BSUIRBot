<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 16.11.17
 * Time: 5:04 PM
 */

namespace BSUIRBot\Exception;


class PhraseNotFound extends \Exception
{
    /**
     * @return string
     */
    public function returnMessage(): string
    {
        return $this->getMessage('Phrase not found: '. $this->getMessage());
    }
}