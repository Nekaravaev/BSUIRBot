<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 20.10.17
 * Time: 3:32 PM
 */

namespace Fixtures;

class BSUIRRequestMock {

    private $_jsonSamplesFolder = __DIR__ . '/../sample/';


    public function send($url, $params = [])
    {
        if (preg_match('/(?<url>http(s):\/\/students\.bsuir\.by\/api\/v1\/studentGroup\/lastUpdateDate\?studentGroup=)(?<group>\d+)/', $url, $matches))
            return $this->isGroupIsset($matches['group']);

        return '';
    }


    /**
     * @return array GroupsList according API
     * @throws \Exception on error with json fetching
     */
    public function getSampleGroupsList():array
    {
        $json = file_get_contents($this->_jsonSamplesFolder  . "groups.json");

        if (!$json)
            throw new \Exception('Can\'t obtain group list');

        return json_decode($json);
    }

    /**
     * @param $gid
     * stub for validation group at BSUIR, based on last update date
     * @return string
     */
    public function isGroupIsset($gid) {
        $groupList = $this->getSampleGroupsList();
        $returnStub = '{"lastUpdateDate": "12.07.2017"}';

        $group = array_filter($groupList, function ($e) use ($gid) {
            return $e->name == $gid;
        });

        return ($group) ? $returnStub : '';
    }



}