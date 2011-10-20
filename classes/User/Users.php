<?php

class Users {

	public static $rolenames = array(
	    User::ROLE_ANON => 'аноним',
	    User::ROLE_READER_UNCONFIRMED => 'неподтвержденный читатель',
	    User::ROLE_READER_CONFIRMED => 'читатель',
	    User::ROLE_SITE_ADMIN => 'администратор сайта',
	);
	private static $users = array();

	public static function getById($id, $data = false) {
		if(!is_numeric($id))
			throw new Exception ($id.' illegal user id');
		if (!isset(self::$users[(int) $id])) {
			$tmp = new User($id, $data);
			self::$users[$tmp->id] = $tmp;
			unset($tmp);
		}
		return self::$users[$id];
	}

	public static function getByIdsLoaded($ids) {
		$out = array();
		$tofetch = array();
		if (is_array($ids)) {
			foreach ($ids as $uid) {
				if (!isset(self::$users[(int) $uid])) {
					$tofetch[] = $uid;
				}
			}
			if (count($tofetch)) {
				$query = 'SELECT * FROM `users` WHERE `id` IN (' . implode(',', $tofetch) . ')';
				$data = Database::sql2array($query, 'id');
			}
			foreach ($ids as $uid) {
				if (!isset(self::$users[(int) $uid])) {
					if (isset($data[$uid])) {
						$tmp = new User($uid, $data[$uid]);
						self::$users[$tmp->id] = $tmp;
						unset($tmp);
					}
				}
			}

			foreach ($ids as $uid) {
				if (isset(self::$users[$uid]))
					$out[$uid] = self::$users[$uid];
			}
		}
		return $out;
	}

}