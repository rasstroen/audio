<?php

class users_module extends BaseModule {

	public $id;
	private $shelfCountOnMain = 5;

	function generateData() {
		global $current_user;

		if (isset($this->params['user_id']) && !is_numeric($this->params['user_id'])) {
			$query = 'SELECT `id` FROM `users` WHERE `nickname`=' . Database::escape($this->params['user_id']);
			$this->params['user_id'] = (int) Database::sql2single($query);
		}

		$this->id = isset($this->params['user_id']) ? (int) $this->params['user_id'] : $current_user->id;
		$this->genre_id = isset($this->params['genre_id']) ? $this->params['genre_id'] : false;

		switch ($this->action) {
			case 'edit':
				switch ($this->mode) {
					default:
						$this->getProfile($edit = true);
						break;
				}
				break;
			case 'show':
				switch ($this->mode) {
					case 'auth':
						$this->getAuth();
						break;
					default:
						$this->getProfile();
						break;
				}
				break;
			case 'list':
				switch ($this->mode) {
					case 'friends':
						$this->getFriends();
						break;
					case 'likes':
						$this->getLikes();
						break;
					case 'followers':
						$this->getFollowers();
						break;
					case 'compare_interests':
						$this->getCompareInterests();
						break;
					default:
						throw new Exception('no mode #' . $this->mode . ' for ' . $this->moduleName);
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function _list($ids, $opts = array(), $limit = false) {
		$users = Users::getByIdsLoaded($ids);
		$out = array();
		/* @var $user User */
		$i = 0;
		if (is_array($users))
			foreach ($users as $user) {
				if ($limit && ++$i > $limit)
					return $out;
				$out[] = $user->getListData();
			}
		return $out;
	}

	// все, кому что-то нравится
	function getLikes() {
		if (!$this->genre_id)
			return;
		$query = 'SELECT * FROM `genre` WHERE `name`=' . Database::escape($this->genre_id);
		$data = Database::sql2row($query);
		if ($data['id']) {
			
		}
	}

	function getCompareInterests() {
		$ids = Database::sql2array('SELECT id FROM users LIMIT 50', 'id');
		$this->data['users'] = $this->_list(array_keys($ids), array(), 15);
		$this->data['users']['link_url'] = 'user/' . $this->params['user_id'] . '/compare';
		$this->data['users']['link_title'] = 'Все единомышленники';
		$this->data['users']['title'] = 'Люди с похожими интересами';
		$this->data['users']['count'] = count($ids);
	}

	function getAuth() {
		global $current_user;
		$this->data['profile']['authorized'] = 0;
		if ($current_user->authorized) {
			// авторизован
			$this->data['profile'] = $current_user->getListData();
			$this->data['profile']['new_messages'] = $current_user->getNewMessagesCount();
			$this->data['profile']['picture'] = $current_user->getAvatar();
			$this->data['profile']['authorized'] = 1;
		}
	}

	function getFriends() {
		global $current_user;
		$user = Users::getById($this->params['user_id']);
		$followingids = $user->getFollowing();
		$this->data['users'] = $this->_list($followingids, array(), 10);
		$this->data['users']['link_url'] = 'user/' . $this->params['user_id'] . '/friends';
		$this->data['users']['link_title'] = 'Все друзья';
		$this->data['users']['title'] = 'Друзья';
		$this->data['users']['count'] = count($followingids);
	}

	function getFollowers() {
		global $current_user;
		/* @var $user User */
		$user = Users::getById($this->params['user_id']);
		$followersids = $user->getFollowers();
		$this->data['users'] = $this->_list($followersids, array(), 10);
		$this->data['users']['link_url'] = 'user/' . $this->params['user_id'] . '/followers';
		$this->data['users']['link_title'] = 'Все поклонники';
		$this->data['users']['title'] = 'Поклонники';
		$this->data['users']['count'] = count($followersids);
	}

	function getProfile($edit =false) {
		global $current_user;
		/* @var $current_user CurrentUser */
		/* @var $user User */
		$user = ($current_user->id === $this->id) ? $current_user : Users::getById($this->id);
		if ($edit && ($user->id != $current_user->id)) {
			Error::CheckThrowAuth(User::ROLE_SITE_ADMIN);
		}

		if ($edit) {
			foreach (Users::$rolenames as $id => $role)
				$this->data['roles'][] = array('id' => $id, 'title' => $role);
		}

		$this->data['profile'] = $user->getXMLInfo();


		$this->data['profile']['role'] = $user->getRole();
		$this->data['profile']['lang'] = $user->getLanguage();
		$this->data['profile']['city_id'] = $user->getProperty('city_id');
		$this->data['profile']['city'] = Database::sql2single('SELECT `name` FROM `lib_city` WHERE `id`=' . (int) $user->getProperty('city_id'));
		$this->data['profile']['picture'] = $user->getAvatar();
		$this->data['profile']['rolename'] = $user->getRoleName();
		$this->data['profile']['bday'] = $user->getBday(date('d-m-Y'), 'd-m-Y');
		$this->data['profile']['path'] = $user->getUrl();
		$this->data['profile']['path_edit'] = $user->getUrl() . '/edit';

		$this->data['profile']['bdays'] = $user->getBday('неизвестно', 'd.m.Y');
		// additional
		$this->data['profile']['link_fb'] = $user->getPropertySerialized('link_fb');
		$this->data['profile']['link_vk'] = $user->getPropertySerialized('link_vk');
		$this->data['profile']['link_tw'] = $user->getPropertySerialized('link_tw');
		$this->data['profile']['link_lj'] = $user->getPropertySerialized('link_lj');

		$this->data['profile']['quote'] = $user->getPropertySerialized('quote');
		$this->data['profile']['about'] = $user->getPropertySerialized('about');
//		$this->data['profile']['path_message'] = Config::need('www_path').'/me/messages?to='.$user->id;
		$this->data['profile']['path_message'] = Config::need('www_path') . '/user/' . $user->getNickName() . '/contact';
	}

	/**
	 * 
	 */
}