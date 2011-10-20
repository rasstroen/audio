<?php

class MessagesWriteModule extends BaseWriteModule {

	function write() {
		global $current_user;
		if (!$current_user->authorized)
			throw new Exception('Access Denied');

		$id_author = $current_user->id;
		$to_users = isset(Request::$post['to']) ? Request::$post['to'] : array($current_user->id);
		if (strstr($to_users, ','))
			$to_users = explode(',', $to_users);
		if (!is_array($to_users))
			$to_users = array($to_users);
		foreach ($to_users as $id) {
			if (strstr($id, ',')) {
				$t_to_users = explode(',', $id);
				foreach ($t_to_users as $n) {
					$to_users_p[(int) $n] = (int) $n;
				}
			}
			else $to_users_p[$id] = (int)$id;
		}

		$to_users = $to_users_p;
		$subject = isset(Request::$post['subject']) ? Request::$post['subject'] : 'Без темы';
		$body = isset(Request::$post['body']) ? Request::$post['body'] : false;
		$subject = prepare_review($subject, '');
		$body = prepare_review($body, '');
		if (!$body)
			throw new Exception('body!');
		$time = time();
		$thread_id = isset(Request::$post['thread_id']) ? Request::$post['thread_id'] : false;

		if ($thread_id) {
			// а можно ли писать в этот тред этому человеку?
			$query = 'SELECT DISTINCT id_recipient FROM `users_messages_index` WHERE `thread_id`=' . $thread_id;
			$usrs = Database::sql2array($query);
			$found = false;
			$to_users = array();
			if ($usrs) {
				foreach ($usrs as $usr) {
					if ($usr['id_recipient'] == $current_user->id)
						$found = true;
					$to_users[$usr['id_recipient']] = $usr['id_recipient'];
				}
			}
			if (!$found)
				throw new Exception('You cant post to thread #' . $thread_id);
		}

		$to_users[$current_user->id] = $current_user->id;

		$this->sendMessage($id_author, $to_users, $subject, $body, $time, $thread_id);
	}

	function sendMessage($id_author, $to_users, $subject, $body, $time, $thread_id = false) {
		if (!is_array($to_users))
			throw new Exception('$to_users must be an array');
		Database::query('START TRANSACTION');
		$query = 'INSERT INTO `users_messages` SET
			`id_author`=' . (int) $id_author . ',
			`time`=' . $time . ',
			`subject`=' . Database::escape($subject) . ',
			`html`=' . Database::escape($body);
		Database::query($query);
		// если есть тред - пишем в тот же тред
		$lastId = Database::lastInsertId();
		$thread_id = $thread_id ? $thread_id : $lastId;
		if ($thread_id) {
			$q = array();
			foreach ($to_users as $receiver_id) {
				if (!$receiver_id)
					continue;
				$is_new = ($receiver_id == $id_author) ? 0 : 1;
				$q[] = '(' . (int) $lastId . ',' . (int) $thread_id . ',' . (int) $receiver_id . ',' . (int) $is_new . ',0)';
			}
			if (count($q)) {
				$query = 'INSERT INTO `users_messages_index`(message_id,thread_id,id_recipient,is_new,is_deleted) VALUES ' . implode(',', $q);
				Database::query($query);
			}
		}
		Database::query('COMMIT');
	}

}
