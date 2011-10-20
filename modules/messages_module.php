<?php

// модуль отвечает за отображение баннеров
class messages_module extends BaseModule {

	function generateData() {
		global $current_user;
		if (!$current_user->id)
			return;

		$this->thread_id = isset($this->params['thread_id']) ? $this->params['thread_id'] : 0;
		switch ($this->action) {
			case 'list':
				switch ($this->mode) {
					case 'thread':
						$this->getThread();
						break;
					default:
						$this->getThreadList();
						break;
				}
				break;
			case 'new':
				$this->getNew();
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getNew() {
		$uid = Request::get(0);
		if ($uid != 'me') {
			if ($uid)
				$uid = Database::sql2single('SELECT `id` FROM `users` WHERE `nickname`=' . Database::escape($uid));
		}
		if($uid)
		XMLClass::$varNode->setAttribute('to', $uid);
		$this->data['message'] = array();
		$this->data['message']['thread_id'] = $this->thread_id;
	}

	function getThread() {
		global $current_user;
		if (!$this->thread_id)
			return;
		$query = 'SELECT M.id_author, M.time, M.subject, M.html,M.id , MI.is_new , MI.message_id FROM `users_messages_index` MI
			LEFT JOIN `users_messages` M ON M.id=MI.message_id
			WHERE `thread_id`=' . $this->thread_id . ' AND `id_recipient`=' . $current_user->id . '
				ORDER BY time DESC';

		$messages = Database::sql2array($query);
		$uids = array();
		$to_new = array();
		foreach ($messages as &$message) {
			if ($message['is_new'])
				$to_new[$message['message_id']] = $message['message_id'];
			$uids[$message['id_author']] = $message['id_author'];
			$message['time'] = date('Y/m/d H:i', $message['time']);
		}

		if (count($to_new)) {
			Database::query('UPDATE `users_messages_index` SET `is_new`=0 WHERE `message_id` IN(' . implode(',', $to_new) . ') AND `id_recipient`=' . $current_user->id);
		}

		$users = Users::getByIdsLoaded($uids);
		foreach ($users as $user) {
			/* @var $user User */
			$this->data['users'][$user->id] = $user->getListData();
		}

		$this->data['messages'] = $messages;
	}

	function getThreadList() {
		global $current_user;
		$out = array();
		$query = 'SELECT * FROM `users_messages_index` UMI
			RIGHT JOIN `users_messages` UM ON UM.id = UMI.message_id
			WHERE `id_recipient`=' . $current_user->id . ' AND `is_deleted`=0';
		$messages = Database::sql2array($query);
		// загрузили все сообщения вообще
		// для каждого треда выбираем последнее сообщение
		$messages_prepared = array();
		$uids = array();
		foreach ($messages as &$message) {
			if (!isset($messages_prepared[$message['thread_id']])) {
				$messages_prepared[$message['thread_id']]['newest']['time'] = 0;
				$messages_prepared[$message['thread_id']]['oldest']['time'] = time() + 10000;
			}
			if ($messages_prepared[$message['thread_id']]['newest']['time'] < $message['time']) {
				$messages_prepared[$message['thread_id']]['newest'] = $message;
				$messages_prepared[$message['thread_id']]['html'] = $message['html'];
			}

			if ($message['is_new'])
				$messages_prepared[$message['thread_id']]['is_new'] = 1;



			if ($messages_prepared[$message['thread_id']]['oldest']['time'] > $message['time']) {
				$messages_prepared[$message['thread_id']]['oldest'] = $message['time'];
				$messages_prepared[$message['thread_id']]['subject'] = $message['subject'];
			}

			$messages_prepared[$message['thread_id']]['members'][$message['id_recipient']] = $message['id_recipient'];
			$messages_prepared[$message['thread_id']]['members'][$message['id_author']] = $message['id_author'];
			$messages_prepared[$message['thread_id']]['thread_id'] = $message['thread_id'];
		}

		foreach ($messages_prepared as $thread_id => &$mess) {
			$mess['oldest'] = date('Y/m/d H:i:s', $mess['oldest']);
			$tmpmess = $mess['newest'];
			$tmpmess['oldest'] = $mess['oldest'];
			$tmpmess['newest'] = date('Y/m/d H:i:s', $mess['newest']['time']);
			$tmpmess['time'] = date('Y/m/d H:i:s', $tmpmess['time']);
			$tmpmess['subject'] = $mess['subject'];
			$tmpmess['is_new'] = isset($mess['is_new']) ? 1 : 0;
			foreach ($mess['members'] as $uid) {
				if ($current_user->id != $uid)
					$tmpmess['members'][] = array(
					    'id' => $uid
					);
				$uids[$uid] = $uid;
			}
			$out[] = $tmpmess;
		}
		$users = Users::getByIdsLoaded($uids);
		foreach ($users as $user) {
			$this->data['users'][$user->id] = $user->getListData();
		}

		$this->data['messages'] = $out;
	}

}