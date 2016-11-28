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
        if ($usersList) {
            return json_decode($usersList, true);
        } else {
            return false;
        }
    }

    public function getCurrentUser($id)
    {
        $currentUser = false;
        $usersList = $this->getUsers();

        if ($usersList) {
                foreach ($usersList as $user) {
                    if ($user['user_id'] == $id && $user['group_id'] != 'temp') {
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

    public function manageUser($id, $params)
    {
        $user = $this->getCurrentUser($id);
        if ($user) {
            $this->updateUser($id, $params);
        } else {
            $this->newUser($id, $params);
        }

        return true;
    }

    private function newUser($id, $params)
    {
        $users = $this->getUsers();
        if ($users) {
            $newUser = ["$id" => array(
                'user_id' => $id,
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
        } else {
            $return = false;
        }

        return $return;
    }

    private function updateUser($id, $params)
    {
        //$userID, $userName, $userDisplayName, $status, $cron
        $users = $this->getUsers();
        foreach ($users as $user => $data) {
                foreach ($data as $value) {
                    if ($value['user_id'] == $id) {
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
