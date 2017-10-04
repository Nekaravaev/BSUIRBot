<?php
/**
     * Created by PhpStorm.
     * User: karavaev
     * Date: 22.11.16
     * Time: 8:42 PM
     */

namespace app\drivers;

class Redis
{
    public $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        if (!$this->redis->ping())
        {
            throw new \Exception('Redis is not available');
        }
    }

    public function ping()
    {
        $ping = $this->redis->ping();
        return ($ping == "+PONG") ? true : false;
    }

    public function getUsers()
    {
        $usersList = $this->redis->sMembers('users');

        return ($usersList) ? $usersList : false;
    }

    public function getCurrentUser($uid)
    {
        $currentUser = false;
        if ($this->redis->hExists("$uid", 'user_id')) {
            $currentUser = $this->redis->hGetAll("$uid");
        }
        return $currentUser;
    }

    public function getUpdatesVKGroup()
    {
        return $this->redis->sGetMembers('VKUpdates');
    }

    public function removeFromUpdatesVKGroup($userId) {
        //$this->redis->sRem("VKUpdates", "VKUser:$userId");
        $this->redis->delete("VKUser:$userId");
        return true;
    }

    public function addToUpdatesVKGroup($userId, $userParams)
    {
        $this->redis->hMset("VKUser:$userId", $userParams);
        $this->redis->sAdd("VKUpdates", "VKUser:$userId");
        return true;
    }

    public function getCronUsers()
    {
        return $this->redis->sGetMembers('cron');
    }

    public function getUsersCount()
    {
        $members = $this->redis->sGetMembers('users');
        return count($members);
    }

    public function manageUser($uid, $params)
    {
        return $this->updateUser($uid, $params);
    }

    public function getGroup($uid)
    {
        if ($this->redis->hExists("user:$uid", 'username')) {
            $group = $this->redis->hGet("user:$uid", 'group_id');
        }
        return ($group) ? $group : false;
    }

    private function updateUser($uid, $params)
    {
        $newUser = [
                'user_id'      => $uid,
                'group_id'     => $params['gid'],
                'username'     => $params['username'],
                'display_name' => $params['display_name'],
                'status'       => $params['status'],
                'cron'         => $params['cron'],
            ];
        $this->redis->hMset("user:$uid", $newUser);
        $this->redis->sAdd("group:" . $params['gid'], "user:$uid");
        if (intval($params['cron'])) {
            $this->redis->sAdd("cron", "user:$uid");
        }
        $this->redis->sAdd('users', "user:$uid");
        return $this->getCurrentUser("user:$uid");
    }

    public function getLatestVKPost()
    {
        $post = (object) $this->redis->hGetAll('latestVKPost');

        return ($post) ? $post : false;

    }

    public function setLatestVKPost($params)
    {
        $post = (array) $params;
        $post['message_raw'] = (array) $params->message_raw;
        return $this->redis->hMset('latestVKPost', $post);
    }
}
