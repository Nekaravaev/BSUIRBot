<?php
/**
 * Created by PhpStorm.
 * User: karavaev
 * Date: 21.10.15
 * Time: 10.47
 */

class User {
    public $folder = 'info';

    public function  __construct($folder) {
        $this->folder = $folder;
    }

    public function getUsers(){
        return file_get_contents($this->folder."/users.json");
    }

    public function getCurrentUser($id){
        $currentUser = false;
        $users = json_decode($this->getUsers());
        foreach ($users->groups as $groups){
            foreach ($groups as $user){
                if ($user->user_id == $id && $user->group_id != 'temp'){
                    $currentUser = $user;
                    break 2;
                }
            }
        }
        return $currentUser;
    }

    public function getUsersCount(){
        $json = json_decode($this->getUsers());
        return $json->users;
    }

    public function manageUser($id,$params) {
       $user = $this->getCurrentUser($id);
            if ($user) {
                $this->updateUser($user->user_id,$params);
            } else {
                $this->newUser($id,$params);
            }
       return true;
    }

    public function getGroup($gid) {
        $groups = json_decode($this->getUsers());
        if (isset ($groups->groups->{$gid})) {
            $group = $groups->groups->{$gid};
        }
        return ($group) ? $group : false;
    }

    private function newUser($id, $params) {
        $users = json_decode($this->getUsers(),true);
        $newUser = array('user_id'      => $id,
                         'group_id'     => $params['gid'],
                         'username'     => $params['username'],
                         'display_name' => $params['display_name'],
                         'status'       => $params['status'],
                         'cron'         => $params['cron']
        );
        if (!$this->getGroup($params['gid'])) $users['groups'][$params['gid']] = array();
        array_push($users['groups'][$params['gid']], $newUser);
        $usersCount = (int) $users['users'];
        $users['users'] = $usersCount + 1;
        $groups = fopen($this->folder."/users.json", "w");
        $result = fwrite($groups, json_encode($users, JSON_PRETTY_PRINT));
        fclose($groups);
        return $result;
    }

    private function updateUser($id, $params){
    //$userID, $userName, $userDisplayName, $status, $cron
        $users = json_decode($this->getUsers(), true);
        $group = $params['gid'];
            foreach ($users['groups'] as $singleGroup => $variables) {
                if ($singleGroup == $group) {
                    foreach ($variables as $value) {
                        if ($value['user_id'] == $id) {
                            $value['group_id']      = $group;
                            $value['username']      = $params['username'];
                            $value['display_name']  = $params['display_name'];
                            $value['status']        = $params['status'];
                            $value['cron']          = $params['cron'];
                            break;
                        }
                    }
                }
            }
        $groups = fopen($this->folder."/users.json", "w");
        $result = fwrite($groups, json_encode($users, JSON_PRETTY_PRINT));
        fclose($groups);
        return $result;
    }
}
