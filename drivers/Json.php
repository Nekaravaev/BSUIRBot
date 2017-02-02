<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.47.
 */

namespace bsuir\drivers;

class Json
{
    public $folder = 'info';

    public function __construct($folder)
    {
        $this->folder = $folder;
    }

    public function getUsers()
    {
        $usersList = file_get_contents($this->folder.'/users.json');

        return ($usersList) ? json_decode($usersList, true) : false;
    }

    public function getCurrentUser($uid)
    {
        $currentUser = false;
        $usersList = $this->getUsers();

        if ($usersList) {
                foreach ($usersList as $user) {
                    if ($user['user_id'] == $uid && $user['group_id'] != 'temp') {
                        $currentUser = $user;
                    }
                }
        }

        return $currentUser;
    }

    public function getCronUsers()
    {
        $usersList = $this->getUsers();
        $cronUsers = array();

        if ($usersList) {
                foreach ($usersList as $user) {
                    if ((boolean) $user['cron'] == true && $user['group_id'] != 'temp') {
                        $cronUsers[] = $user;
                    }
                }
        }

        return $cronUsers;
    }

    public function getUsersCount()
    {
        $json = $this->getUsers();

        return count($json);
    }

    public function manageUser($uid, $params)
    {
        $user = $this->getCurrentUser($uid);
        if ($user) {
            $this->updateUser($uid, $params);
        } else {
            $this->newUser($uid, $params);
        }

        return true;
    }

    private function newUser($uid, $params)
    {
        $users = $this->getUsers();
        if ($users) {
            $newUser = ["$uid" => array(
                'user_id' => $uid,
                'group_id' => $params['gid'],
                'username' => $params['username'],
                'display_name' => $params['display_name'],
                'status' => $params['status'],
                'cron' => $params['cron'],
            )];
            array_push($users, $newUser);
            $usersList = fopen($this->folder.'/users.json', 'w');
            $result = fwrite($usersList, json_encode($users, JSON_PRETTY_PRINT));
            fclose($usersList);
            $return = $result;
        }

        return ($return) ? $return : false;
    }

    private function updateUser($uid, $params)
    {
        //$userID, $userName, $userDisplayName, $status, $cron
        $users = $this->getUsers();
        foreach ($users as $user => $data) {
                foreach ($data as $value) {
                    if ($value['user_id'] == $uid) {
                        $value['group_id'] = $params['gid'];
                        $value['username'] = $params['username'];
                        $value['display_name'] = $params['display_name'];
                        $value['status'] = $params['status'];
                        $value['cron'] = $params['cron'];
                        break;
                    }
                }
        }
	    $usersList = fopen($this->folder.'/users.json', 'w');
        $result = fwrite($usersList, json_encode($users, JSON_PRETTY_PRINT));
        fclose($usersList);

        return $result;
    }
}
