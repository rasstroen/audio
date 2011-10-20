<?php

class SeriesWriteModule extends BaseWriteModule {

	function _new() {
		global $current_user;
		$parent_id = isset(Request::$post['id_parent']) ? Request::$post['id_parent'] : false;
		$parent_id = max(0, (int) $parent_id);


		$title = isset(Request::$post['title']) ? Request::$post['title'] : false;
		$description = isset(Request::$post['description']) ? Request::$post['description'] : false;


		if ($parent_id) {
			$query = 'SELECT `id` FROM `series` WHERE `id`=' . $parent_id;
			if (!Database::sql2single($query))
				throw new Exception('No such parent');
		}

		if (!$title)
			throw new Exception('Empty title');

		$description = prepare_review($description);
		$title = prepare_review($title, '');
		$new = array(
		    'description' => $description,
		    'title' => $title,
		    'id_parent' => (int) $id_parent,
		);
		$old = array(
		    'description' => '',
		    'title' => '',
		    'id_parent' => 0,
		);

		Database::query('START TRANSACTION');
		$query = 'INSERT INTO `series` SET `id_parent`=' . $parent_id . ',`title`=' . Database::escape($title) . ', `description`=' . Database::escape($description);
		Database::query($query);
		$id = Database::lastInsertId();
		if (!$id)
			throw new Exception('Cant save serie');

		SerieLog::addLog($new, $old, $id);
		SerieLog::saveLog($id, BookLog::TargetType_serie, $current_user->id, BiberLog::BiberLogType_serieNew);
		Database::query('COMMIT');
		ob_end_clean();
		header('Location:' . Config::need('www_path') . '/s/' . $id);
		Database::query('COMMIT');
		exit();
	}

	function write() {
		global $current_user;
		if (!$current_user->authorized)
			throw new Exception('Access Denied');

		$id = isset(Request::$post['id']) ? Request::$post['id'] : 0;
		$id = max(0, (int) $id);
		if (!$id) {
			$this->_new();
			return;
		}

		$query = 'SELECT * FROM `series` WHERE `id`=' . $id;
		$old = Database::sql2row($query);
		if (!$old || !$old['id'])
			throw new Exception('no such serie #' . $id);

		$parent_id = isset(Request::$post['id_parent']) ? Request::$post['id_parent'] : false;
		$parent_id = max(0, (int) $parent_id);
		if (!$id)
			throw new Exception('Illegal id');

		$title = isset(Request::$post['title']) ? Request::$post['title'] : false;
		$description = isset(Request::$post['description']) ? Request::$post['description'] : false;


		if ($parent_id == $id)
			throw new Exception('Illegal parent');

		if ($parent_id) {
			$query = 'SELECT `id` FROM `series` WHERE `id`=' . $parent_id;
			if (!Database::sql2single($query))
				throw new Exception('No such parent');
		}

		if (!$title)
			throw new Exception('Empty title');

		$description = prepare_review($description);
		$title = prepare_review($title, '');
		$new = array(
		    'description' => $description,
		    'title' => $title,
		    'id_parent' => (int) $id_parent,
		);
		Database::query('START TRANSACTION');
		SerieLog::addLog($new, $old , $id);
		SerieLog::saveLog($id, BookLog::TargetType_serie, $current_user->id, BiberLog::BiberLogType_serieEdit);

		$query = 'UPDATE `series` SET `id_parent`=' . $parent_id . ',`title`=' . Database::escape($title) . ', `description`=' . Database::escape($description) . ' WHERE `id`=' . $id;
		Database::query($query);
		Database::query('COMMIT');
	}

}