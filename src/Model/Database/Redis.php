<?php
/**
     * Created by PhpStorm.
     * User: karavaev
     * Date: 22.11.16
     * Time: 8:42 PM
     */

namespace BSUIRBot\Model\Database;

use BSUIRBot\Model\User;

class Redis
{
    public $redis;

    /**
     * Redis constructor
     * @throws \Exception
     */
    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
        if (!$this->redis->ping())
        {
            throw new \Exception('Redis is not available');
        }
    }

    /**
     * Ping redis server
     * return bool status of connection
     */
    public function ping(): bool
    {
        $ping = $this->redis->ping();
        return ($ping === "+PONG") ? true : false;
    }

    /**
     * Obtain list of users from set
     * @return array list of users
     */
    public function getUsers(): array
    {
        return $this->redis->sMembers('users');
    }

    /**
     * @param int $uid
     * @return \stdClass with user
     */
    public function getUser(int $uid): \stdClass
    {
        $currentUser = [];
        if ($this->redis->hExists("user:$uid", 'id')) {
            $currentUser = $this->redis->hGetAll("user:$uid");
        }
        return (object) $currentUser;
    }

    /**
     * Obtain list of people, who want to receive
     * wall posts directly to messages.
     *
     * @return array list of users (display_name, user_id)
     */
    public function getFollowersVKUpdates(): array
    {
        return $this->redis->sGetMembers('VKUpdates');
    }


    public function removeFromFollowersVKGroup($userId) {
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

    public function getGroup($uid)
    {
        if ($this->redis->hExists("user:$uid", 'username')) {
            $group = $this->redis->hGet("user:$uid", 'group_id');
        }
        return ($group) ? $group : false;
    }

    public function updateUser(User $user): bool
    {
        $uid = $user->getUserId();
        $arr = $user->attributes();

        $this->redis->hMset("user:$uid", $arr);
        $this->redis->sAdd("group:" . $user->getGroupId(), "user:$uid");

        if (intval($user->isCron())) {
            $this->redis->sAdd("cron", "user:$uid");
        }
        $this->redis->sAdd('users', "user:$uid");
        return true;
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
