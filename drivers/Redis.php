<?php
/**
     * Created by PhpStorm.
     * User: karavaev
     * Date: 22.11.16
     * Time: 8:42 PM
     */

namespace bsuir\drivers;

class Redis
{
    public $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function ping()
    {
	    $ping = $this->redis->ping();
        return ($ping == "+PONG") ? true : false;
    }

	public function getUsers()
	{
		$usersList = $this->redis->sMembers('users');
		if ($usersList) {
			return $usersList;
		} else {
			return false;
		}
	}

	public function getCurrentUser($id)
	{
		$currentUser = false;
		if ($this->redis->hExists("user:$id", 'username')) {
			$currentUser = $this->redis->hGetAll("user:$id");
		}
		return $currentUser;
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

	public function manageUser($id, $params)
	{
		return $this->updateUser($id, $params);
	}

	public function getGroup($id)
	{
		if ($this->redis->hExists("user:$id", 'username')) {
			$group = $this->redis->hGet("user:$id", 'group_id');
		}
		return ($group) ? $group : false;
	}

	private function updateUser($id, $params)
	{
		if ($this->ping()) {
			$newUser = [
				'user_id'      => $id,
				'group_id'     => $params['gid'],
				'username'     => $params['username'],
				'display_name' => $params['display_name'],
				'status'       => $params['status'],
				'cron'         => $params['cron'],
			];
			$this->redis->hMset( "user:$id", $newUser );
			$this->redis->sAdd( "group:" . $params['gid'], "user:$id" );
			if ($params['cron']) {
				$this->redis->sAdd( "cron", "user:$id" );
			}
			return true;
		} else
			return false;
	}
}
