<?php

class AuthorWriteModule extends BaseWriteModule {

	function newAuthor() {
		global $current_user;
		// добавляем книгу
		$fields = array(
		    'lang_code' => 'author_lang', //lang_code
		    'bio' => 'bio',
		    'first_name' => 'first_name',
		    'middle_name' => 'middle_name',
		    'last_name' => 'last_name',
		    //'id_user' => 'id_user',
		    'homepage' => 'homepage',
		    'wiki_url' => 'wiki_url',
		    'date_birth' => 'date_birth',
		    'date_death' => 'date_death'
		);

		Request::$post['lang_code'] = Config::$langs[Request::$post['lang_code']];
		$to_update = array();





		foreach ($fields as $field => $personfield) {
			if (!isset(Request::$post[$field])) {
				throw new Exception('field missed #' . $field);
			}
			$to_update[$personfield] = Request::$post[$field];
		}

		$q = array();
		if (count($to_update))
			$to_update['authorlastSave'] = time();

		foreach ($to_update as $field => $value) {
			if ($field == 'date_birth' || $field == 'date_death') {
				$value = getDateFromString($value);
			}
			$person = new Person();
			if ($field == 'bio') {
				list($full, $short) = $person->processBio($value);
				$q[] = '`bio`=' . Database::escape($full) . '';
				$q[] = '`short_bio`=' . Database::escape($short) . '';
			} else {
				$q[] = '`' . $field . '`=' . Database::escape($value) . '';
			}
		}

		if (count($q)) {
			$query = 'INSERT INTO `persons` SET ' . implode(',', $q);
			Database::query($query);
			$lid = Database::lastInsertId();
			if ($lid) {
				if (isset($_FILES['picture']) && $_FILES['picture']['tmp_name']) {
					$folder = Config::need('static_path') . '/upload/authors/' . (ceil($lid / 5000));
					@mkdir($folder);
					chmod($folder, 755);
					$filename = $folder . '/' . $lid . '.jpg';
					$upload = new UploadAvatar($_FILES['picture']['tmp_name'], 100, 100, "simple", $filename);
					if ($upload->out) {
						$query = 'UPDATE `persons` SET `has_cover`=1 WHERE `id`=' . $lid;
						Database::query($query);
					} else {
						throw new Exception('cant copy file to ' . $filename, 100);
					}
				}
				unset($to_update['authorlastSave']);
				PersonLog::addLog($to_update, array(), $lid);
				PersonLog::saveLog($lid, BookLog::TargetType_person, $current_user->id, BiberLog::BiberLogType_personNew);
				ob_end_clean();
				header('Location:' . Config::need('www_path') . '/a/' . $lid);
				exit();
			}
		}
	}

	function write() {
		global $current_user;
		$id = isset(Request::$post['id']) ? (int) Request::$post['id'] : false;

		if (!$id)
			$this->newAuthor();


		$person = Persons::getInstance()->getByIdLoaded($id);
		if (!$person)
			return;
		$savedData = $person->data;
		/* @var $book Book */

		$fields = array(
		    'lang_code' => 'author_lang', //lang_code
		    'bio' => 'bio',
		    'first_name' => 'first_name',
		    'middle_name' => 'middle_name',
		    'last_name' => 'last_name',
		    //'id_user' => 'id_user',
		    'homepage' => 'homepage',
		    'wiki_url' => 'wiki_url',
		    'date_birth' => 'date_birth',
		    'date_death' => 'date_death'
		);

		Request::$post['lang_code'] = Config::$langs[Request::$post['lang_code']];
		$to_update = array();
		if (isset($_FILES['picture']) && $_FILES['picture']['tmp_name']) {
			$folder = Config::need('static_path') . '/upload/authors/' . (ceil($person->id / 5000));
			@mkdir($folder);
			@chmod($folder, 755);
			$filename = $folder . '/' . $person->id . '.jpg';
			$upload = new UploadAvatar($_FILES['picture']['tmp_name'], 100, 100, "simple", $filename);
			if ($upload->out)
				$to_update['has_cover'] = 1;
			else {
				throw new Exception('cant copy file to ' . $filename, 100);
			}
		}


		foreach ($fields as $field => $personfield) {
			if (!isset(Request::$post[$field])) {
				throw new Exception('field missed #' . $field);
			}
			if ($person->data[$personfield] != Request::$post[$field]) {
				$to_update[$personfield] = Request::$post[$field];
			}
		}

		$q = array();
		if (count($to_update))
			$to_update['authorlastSave'] = time();


		foreach ($to_update as $field => &$value) {
			if ($field == 'date_birth' || $field == 'date_death') {
				$value = getDateFromString($value);
			}

			if ($field == 'bio') {
				list($full, $short) = $person->processBio($value);
				$q[] = '`bio`=' . Database::escape($full) . '';
				$q[] = '`short_bio`=' . Database::escape($short) . '';
				$value = $person->data['bio'] = $full;
				$person->data['short_bio'] = $short;
			} else {
				$q[] = '`' . $field . '`=' . Database::escape($value) . '';
				$person->data[$field] = $value;
			}
		}

		if (count($q)) {
			$query = 'UPDATE `persons` SET ' . implode(',', $q) . ' WHERE `id`=' . $person->id;
			Database::query($query);
			unset($to_update['authorlastSave']);
			PersonLog::addLog($to_update, $savedData,$person->id);
			PersonLog::saveLog($person->id, BiberLog::TargetType_person, $current_user->id, BiberLog::BiberLogType_personEdit);
			Persons::getInstance()->dropCache($person->id);
		}
		ob_end_clean();
		header('Location:' . Config::need('www_path') . '/a/' . $person->id);
		exit();
	}

}