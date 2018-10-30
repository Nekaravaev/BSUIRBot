<?php
/**
     * Created by PhpStorm.
     * User: karavaev
     * Date: 22.11.16
     * Time: 8:42 PM
     */

namespace BSUIRBot\Model\Database;
use BSUIRBot\Exception\GroupNotFoundException;
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
            return $this->redis->hGet("user:$uid", 'group_id');
        }
        throw new GroupNotFoundException();
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

    /**
     * @param int $id
     * @return boolean
     */
    public function switchDatabase(int $id) {
        return $this->redis->select($id);
    }

    public function setBSUIRGroups(array $list) {

        foreach ($list as $group) {
            $this->redis->hMSet("group:{$group['name']}", $group);

            $this->redis->sAdd("groups", "group:{$group['name']}");
        }

        return true;
    }

    public function getBSUIRGroups(): array {

        $groups = [];
        $groupsDBList = $this->redis->sMembers('groups');

        foreach ($groupsDBList as $groupName) {
            $groups[] = $this->redis->hGetAll($groupName);
        }

        return $groups;
    }
}
