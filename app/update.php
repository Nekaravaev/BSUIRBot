<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 12.9.15
 * Time: 1.02
 */

class Cron {
    function __construct(){
      return $this->updateGroups();
    }

    protected function updateGroups() {
        $xml = simplexml_load_file('http://www.bsuir.by/schedule/rest/studentGroup');
        $groups = fopen("../groups/groups.json", "w");
        $result = fwrite($groups, json_encode($xml));
        fclose($groups);
        return $result;
    }
}

new Cron();

?>