<?php

class Jusers_module extends JBaseModule {

	function process() {
		global $current_user;
		$current_user = new CurrentUser();
		switch ($_POST['action']) {
			case 'add_loved':
				$this->addLoved();
				break;
			case 'check_loved':
				$this->checkLoved();
				break;
			case 'toggle_vandal':
				$this->toggle_vandal();
				break;
		}
	}

	function error($s = 'ошибка') {
		$this->data['success'] = 0;
		$this->data['error'] = $s;
		return;
	}

	function toggle_vandal() {
		global $current_user;
		$this->data['success'] = 0;
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		if ($current_user->getRole() < User::ROLE_BIBER) {
			$this->error('Must be biber');
			return;
		}

		$target_id = isset($_POST['id']) ? (int) $_POST['id'] : false;
		if (!$target_id) {
			$this->error('Illegal id');
			return;
		}

		/* @var $target_user CurrentUser */
		$target_user = Users::getByIdsLoaded(array($target_id));
		if (!isset($target_user[$target_id])) {
			$this->error('No user #' . $target_id);
			return;
		}
		
		
		$target_user = $target_user[$target_id];

		if ($target_id == $current_user->id) {
			$this->error('Онанизм');
			return;
		}

		$oldRole = $target_user->getRole();
		
		if ($oldRole < User::ROLE_VANDAL) {
			$this->error('Too small role');
			return;
		}
		if ($oldRole >= User::ROLE_BIBER) {
			$this->error('Too large role');
			return;
		}

		if ($oldRole == User::ROLE_VANDAL) {
			$query = 'UPDATE `users` SET `role`=' . User::ROLE_READER_CONFIRMED . ' WHERE `id`=' . $target_user->id;
			Database::query($query);
			$this->data['user_role'] = User::ROLE_READER_CONFIRMED;
			$this->data['success'] = 1;
			Users::dropCache($target_user->id);
			return;
		}

		if ($oldRole < User::ROLE_SITE_ADMIN) {
			$query = 'UPDATE `users` SET `role`=' . User::ROLE_VANDAL . ' WHERE `id`=' . $target_user->id;
			Database::query($query);
			$this->data['user_role'] = User::ROLE_VANDAL;
			$this->data['success'] = 1;
			Users::dropCache($target_user->id);
			return;
		}
		
		$this->data['user_role'] = $oldRole;
		$this->data['error'] = '?';
	}

	function checkLoved() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		$item_type = isset($_POST['item_type']) ? $_POST['item_type'] : false;
		$item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : false;
		if (!$item_type || !$item_id) {
			$this->error('item_id or item_type missed');
			return;
		}

		if (!isset(Config::$loved_types[$item_type])) {
			$this->error('illegal item_type#' . $item_type);
			return;
		}

		$query = 'SELECT COUNT(1) as cnt FROM `users_loved` WHERE `id_target`=' . $item_id . ' AND `target_type`=' . Config::$loved_types[$item_type] . ' AND `id_user`=' . $current_user->id;
		if (Database::sql2single($query, false)) {
			$this->data['success'] = 1;
			$this->data['in_loved'] = 1;
			return;
		} else {
			$this->data['success'] = 1;
			$this->data['in_loved'] = 0;
		}
	}

	function addLoved() {
		global $current_user;
		$event = new Event();
		/* @var $current_user CurrentUser */
		if (!$current_user->authorized) {
			$this->error('Auth');
			return;
		}

		$item_type = isset($_POST['item_type']) ? $_POST['item_type'] : false;
		$item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : false;
		if (!$item_type || !$item_id) {
			$this->error('item_id or item_type missed');
			return;
		}

		if (!isset(Config::$loved_types[$item_type])) {
			$this->error('illegal item_type#' . $item_type);
			return;
		}

		$query = 'INSERT INTO `users_loved` SET `id_target`=' . $item_id . ',`target_type`=' . Config::$loved_types[$item_type] . ',`id_user`=' . $current_user->id;
		if (Database::query($query, false)) {
			$this->data['success'] = 1;
			$this->data['item_id'] = $item_id;
			$this->data['in_loved'] = 1;
			$event->event_LovedAdd($current_user->id, $item_id, $item_type);
			$event->push();
			if ($item_type == 'book') {
				$time = time();
				// inserting a new mark
				$query = 'INSERT INTO `book_rate` SET `id_book`=' . $item_id . ',`id_user`=' . $current_user->id . ',`rate`=5,`time`=' . $time . ' ON DUPLICATE KEY UPDATE
				`rate`=5 ,`time`=' . $time . ',`with_review`=0';
				Database::query($query);
				//recalculating rate
				$query = 'SELECT COUNT(1) as cnt, SUM(`rate`) as rate FROM `book_rate` WHERE `id_book`=' . $item_id;
				$res = Database::sql2row($query);
				$book_mark = round($res['rate'] / $res['cnt'] * 10);
				$query = 'UPDATE `book` SET `mark`=' . $book_mark . ' WHERE `id`=' . $item_id;
				Database::query($query);
			}
			return;
		} else {
			$query = 'DELETE FROM `users_loved` WHERE `id_target`=' . $item_id . ' AND `target_type`=' . Config::$loved_types[$item_type] . ' AND `id_user`=' . $current_user->id;
			if (Database::query($query, false)) {
				$this->data['success'] = 1;
				$this->data['item_id'] = $item_id;
				$this->data['in_loved'] = 0;
				return;
			} else {
				$this->data['success'] = 0;
			}
		}
	}

}