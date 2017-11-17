<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 27.02.17
 * Time: 9:23 PM
 */

namespace BSUIRBot\Config;

use BSUIRBot\Exception\BreakException;

class ProdConfig extends Config
{
    public  $environment = 'production';

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }


    /**
     * @return string
     */
    public function getBugSnagAPI(): string
    {
        return $this->bugSnagAPI;
    }
}