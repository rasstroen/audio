<?php

class GenreWriteModule extends BaseWriteModule {

	function write() {
		global $current_user;
		if (!$current_user->authorized)
			throw new Exception('Access Denied');

		
		$id = isset(Request::$post['id']) ? Request::$post['id'] : 0;
		$id = max(0, (int) $id);
		
		$row = Database::sql2row('SELECT * FROM genre WHERE `id`='.$id);
		if(!$row)
			return;

		if (!$id)
			throw new Exception('Illegal id');

		$description = prepare_review(isset(Request::$post['description']) ? Request::$post['description'] : '');

		if (!$description)
			throw new Exception('Empty description');

		$description = prepare_review($description);


		$query = 'UPDATE `genre` SET `description`=' . Database::escape($description) . ' WHERE `id`=' . $id;
		Database::query($query);
		ob_end_clean();
		header('Location:' . Config::need('www_path') . '/genres/' . $row['name']);
		exit();
	}

}