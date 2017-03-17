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

    /**
     * @return array with dataProvider
     */
    public function additionTimestamps()
    {
        return ['1489611600' => ['1489611600', '1', '4'],
                '1489520639' => ['1489520639', '1', '2']
            ];
    }

    /**
     * @dataProvider additionTimestamps
     * @param $timestamp int|string timestamp
     * @param $week int|string student week number
     * @param $day int|string day number
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

    /**
     * @return array with dataProvider
     */

    public function additionDayNumbers()
    {
        return [
          [0, 'Понедельник'],
          [1, 'Вторник'],
          [2, 'Среда'],
          [5, 'Суббота'],
          [6, 'Воскресенье']
        ];

    }

    /**
     * @dataProvider additionDayNumbers
     * @param $number int|string day number
     * @param $name string day name that would be return
     */

     public function testGetDayNameByNumber($number, $name)
     {
        $dayName = BSUIR::getDayNameByNumber($number);

        $this->assertEquals($name, $dayName);
     }

     public function testUpdateGroups()
     {
        $result = BSUIR::updateGroups();

        $this->assertTrue($result);
     }

     public function testGetGroupID()
     {


     }

     public function testGetGroupIDWrongData()
     {
         $result = BSUIR::getGroupID(1);

         $this->expectExceptionMessage('Группа не найдена. Введите другую.');
     }
}
