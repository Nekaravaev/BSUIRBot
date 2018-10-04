<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 15.03.17
 * Time: 11:51 AM
 */
namespace tests\unit;

use BSUIRBot\Model\BSUIR;
use Fixtures\BSUIRRequestMock;
use PHPUnit\Framework\TestCase;

class BSUIRTest extends TestCase
{
    /** @var BSUIR */
    private $class;

    public function setUp()
    {
        $request = new BSUIRRequestMock();
        $this->class = new BSUIR($request);
    }

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
//
//    /**
//     * @dataProvider additionTimestamps
//     * @param $timestamp int|string timestamp
//     * @param $week int|string student week number
//     * @param $day int|string day number
//     */
//
//    public function testGetData($timestamp, $week, $day)
//    {
//        $date = BSUIR::getDayAndWeekByDate($timestamp);
//
//        $expect = [
//            'week' => $week,
//            'day'  => $day
//        ];
//
//        $this->assertEquals($expect, $date);
//    }

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
        $dayName = $this->class->getDayNameByNumber($number);

        $this->assertEquals($name, $dayName);
    }


    /**
     * return dataprovider for isset testing
     */
    public function currentsGroupsProvider(): array
    {
        return [
            ['581062', true],
            ['58106255', false],
            ['AAA', false]
        ];
    }

    /**
     * @var string $gid group id to isset check
     * @var bool $isset is actually set or not
     * @dataProvider currentsGroupsProvider
     */
    public function testGetGroupID(string $gid, $isset)
    {
        $isSet = $this->class->isGroupIsset($gid);
        
        $this->assertSame($isSet, $isset);
    }

    /**
     * return dataprovider for isset testing
     */
    public function updateDateValidProvider(): array
    {
        return [
            ['581062', '12.07.2017'],
            ['581063', '12.07.2017']
        ];
    }

    /**
     * return dataprovider for isset testing, invalid data
     */
    public function updateDateInvalidProvider(): array
    {
        return [
            ['AAA', null],
            ['55', false]
        ];
    }

    /**
     * @dataProvider updateDateValidProvider
     *
     * @param $gid
     * @param $expectedDate
     *
     * 
     */
    public function testScheduleLastUpdateDate($gid, $expectedDate) {
        $date = $this->class->getScheduleLastUpdateDate($gid);

        return $this->assertEquals($date, $expectedDate);
    }

    /**
     * @dataProvider updateDateInvalidProvider
     *
     * @param $gid
     * @param $expectedDate

     *
     * @return void
     */
    public function testInvalidScheduleLastUpdateDate($gid, $expectedDate) {
        $this->expectException(\Exception::class);
        $this->class->getScheduleLastUpdateDate($gid);
    }
}
