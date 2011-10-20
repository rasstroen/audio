<?php

// класс, отвечающий за текущего юзера
class CurrentUser extends User {

	public $xml_fields = array(// в отличие от всех юзеров для текущего мы можем выдавать больше данных
	    'id',
	    'email',
	    'role',
	    'nickname',
	    'lastSave',
	);
	public $authorized = false;
	public $hash = '';

	function __construct() {
		parent::__construct();
		$this->uid = $this->authorize_cookie();
	}

	public function getProperty($field, $default = false) {
		return isset($this->profile[$field]) ? $this->profile[$field] : $default;
	}

	public function logout() {
		$this->authorized = false;
		$this->id = 0;
		$this->setAuthCookie($value, true);
	}

	// именно залогинились
	public function onLogin() {
		$hash = md5($this->id . ' ' . time() . ' ' . rand(1, 1000));
		$query = 'INSERT INTO `users_session` SET
			`user_id`=' . $this->id . ',
			`session`=\'' . $hash . '\',
			`expires`=' . (time() + Config::need('auth_cookie_lifetime')) . '
			ON DUPLICATE KEY UPDATE
			`session`=\'' . $hash . '\',
			`expires`=' . (time() + Config::need('auth_cookie_lifetime'));
		Database::query($query);
		$this->setProperty('lastLogin', time());
		$this->setAuthCookie($hash);
	}

	public function getAvailableNickname($nickname, $additional = '') {
		$nickname = trim($nickname) . $additional;
		$query = 'SELECT `nickname` FROM `users` WHERE `nickname` LIKE \'' . $nickname . '\' LIMIT 1';
		$row = Database::sql2single($query);
		if ($row && $row['nickname']) {
			return $this->getAvailableNickname($nickname, $additional . rand(1, 99));
		}
		return $nickname;
	}

	private function setAuthCookie($value, $delete = false) {
		if ($delete) {
			$time = time() - 1;
		} else {
			$time = time() + Config::need('auth_cookie_lifetime');
		}
		Request::headerCookie(Config::need('auth_cookie_hash_name'), $value, $time, '/', Config::need('www_domain'), false, true);
		Request::headerCookie(Config::need('auth_cookie_id_name'), $this->id, time() + Config::need('auth_cookie_lifetime'), '/', Config::need('www_domain'), false, true);
	}

	// авторизуем пользователя по кукам
	public function authorize_cookie() {
		$auth_cookie_name = Config::need('auth_cookie_hash_name');
		$auth_uid_name = Config::need('auth_cookie_id_name');
		if (isset($_COOKIE[$auth_cookie_name]) && isset($_COOKIE[$auth_uid_name])) {
			$query = 'SELECT `session`,`expires` FROM `users_session` WHERE `user_id`=' . (int) $_COOKIE[$auth_uid_name];
			$row = Database::sql2row($query);
			if ($row) {
				if ($row['session'] == $_COOKIE[$auth_cookie_name] && $row['expires'] > time()) {
					$this->id = (int) $_COOKIE[$auth_uid_name];
					$this->load();
					$this->authorized = true;
				}
			}
		}else
			return false;
	}

	// авторизуем пользователя по логину и паролю
	public function authorize_password($email, $password, $md5used = false) {
		$row = Database::sql2row('SELECT * FROM `users` WHERE 
			(`email`=\'' . $email . '\' OR 
			`nickname`=\'' . $email . '\')');
		if (!$row) {
			// нет такого пользователя
			return 'user_missed';
		}

		$password = $md5used ? $password : md5($password);
		if ($row) {
			if ($password != $row['password']) {
				return 'user_password';
			}
		}
		$this->load($row);
		$this->authorized = true;
		$this->onLogin();
		return true;
	}

}