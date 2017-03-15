<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 15.03.17
 * Time: 11:51 AM
 */
namespace app\tests;

use app\models\BSUIR;
use PHPUnit\Framework\TestCase;

class BSUIRTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        date_default_timezone_set("Europe/Minsk");
        parent::__construct($name, $data, $dataName);
    }

    public function additionTimestamps()
    {
        return ['1489611600' => ['1489611600', '1', '4'],
                '1489520639' => ['1489520639', '1', '2']
            ];

    }

    /**
     * @dataProvider additionTimestamps
     */
    public function testGetData($timestamp, $week, $day)
    {

        $date = BSUIR::getDate($timestamp);

        $expect = [
            'week' => $week,
            'day'  => $day
        ];

        $this->assertEquals($expect, $date);
    }
}
