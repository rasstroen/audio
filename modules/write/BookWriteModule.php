<?php

class BookWriteModule extends BaseWriteModule {

	function newBook() {
		// добавляем книгу
		global $current_user;

		$fields = array(
		    'title' => 'title',
		    'subtitle' => 'subtitle',
		    'isbn' => 'ISBN',
		    'year' => 'year',
		    'lang_code' => 'id_lang', //lang_code
		    'annotation' => 'description',
		);


		Request::$post['lang_code'] = Config::$langs[Request::$post['lang_code']];

		Request::$post['title'] = trim(prepare_review(Request::$post['title'], ''));
		Request::$post['annotation'] = trim(prepare_review(Request::$post['annotation']), false, '<img>');


		if (!Request::$post['title']) {
			throw new Exception('title missed');
		}

		foreach ($fields as $field => $bookfield) {
			if (!isset(Request::$post[$field])) {
				throw new Exception('field missed #' . $field);
			}

			$to_update[$bookfield] = Request::$post[$field];
		}

		$q = array();
		foreach ($to_update as $field => $value) {
			if (in_array($field, array('ISBN', 'year'))) {
				$value = (int) $value;
			}
			$q[] = '`' . $field . '`=' . Database::escape($value) . '';
		}
		$q[] = '`add_time`=' . time();

		if (count($q)) {
			$query = 'INSERT INTO `book` SET ' . implode(',', $q);
			Database::query($query);
			if ($lid = Database::lastInsertId()) {
				if (Request::$post['n'] && Request::$post['m']) {
					// журнал - вставляем
					$query = 'INSERT INTO `book_magazines` SET `id_book`=' . $lid . ',id_magazine=' . (int) Request::$post['m'] . ',`year`=' . $to_update['year'] . ',`n`=' . (int) Request::$post['n'];
					Database::query($query, false);
				}

				if (isset(Request::$post['author_id']) && Request::$post['author_id']) {
					// журнал - вставляем
					$query = 'INSERT INTO `book_persons` SET `id_book`=' . $lid . ',`id_person`=' . (int) Request::$post['author_id'] . ',`person_role`=' . Book::ROLE_AUTHOR;
					Database::query($query, false);
					BookLog::addLog(array('id_person' => (int) Request::$post['author_id'], 'person_role' => Book::ROLE_AUTHOR), array('id_person' => 0, 'person_role' => 0), $lid);
					BookLog::saveLog($lid, BookLog::TargetType_book, $current_user->id, BiberLog::BiberLogType_bookEditPerson);
				}

				if (isset($_FILES['cover']) && $_FILES['cover']['tmp_name']) {
					$folder = Config::need('static_path') . '/upload/covers/' . (ceil($lid / 5000));
					@mkdir($folder);
					chmod($folder, 755);
					$filename = $folder . '/' . $lid . '.jpg';
					$upload = new UploadAvatar($_FILES['cover']['tmp_name'], 100, 100, "simple", $filename);
					if ($upload->out) {
						$query = 'UPDATE `book` SET `is_cover`=1 WHERE `id`=' . $lid;
						Database::query($query);
					} else {
						throw new Exception('cant copy file to ' . $filename, 100);
					}
				}
				BookLog::addLog($to_update, array(), $lid);
				BookLog::saveLog($lid, BookLog::TargetType_book, $current_user->id, BiberLog::BiberLogType_bookNew);
				ob_end_clean();
				$event = new Event();
				$event->event_BooksAdd($current_user->id, $lid);
				$event->push();
				header('Location:' . Config::need('www_path') . '/b/' . $lid);
				Database::query('COMMIT');
				exit();
			}
		}
	}

	function write() {
		global $current_user;
		Database::query('START TRANSACTION');
		if (!$current_user->authorized)
			throw new Exception('Access Denied');
		if (!isset(Request::$post['lang_code']) || !(Request::$post['lang_code']))
			Request::$post['lang_code'] = Config::need('default_language');

		$id = isset(Request::$post['id']) ? (int) Request::$post['id'] : false;

		if (!$id) {
			$this->newBook();
			return;
		}


		$books = Books::getInstance()->getByIdsLoaded(array($id));
		$book = is_array($books) ? $books[$id] : false;
		if (!$book)
			return;
		/* @var $book Book */

		$fields = array(
		    'title' => 'title',
		    'subtitle' => 'subtitle',
		    'isbn' => 'ISBN',
		    'year' => 'year',
		    'lang_code' => 'id_lang', //lang_code
		    'annotation' => 'description',
		);

		Request::$post['lang_code'] = Config::$langs[Request::$post['lang_code']];
		Request::$post['annotation'] = trim(prepare_review(Request::$post['annotation'], false, '<img>'));
		Request::$post['title'] = trim(prepare_review(Request::$post['title'], ''));
		
		$to_update = array();
		if (isset(Request::$post['quality'])) {
			$to_update['quality'] = (int) Request::$post['quality'];
		}
		if (isset($_FILES['cover']) && $_FILES['cover']['tmp_name']) {
			$folder = Config::need('static_path') . '/upload/covers/' . (ceil($book->id / 5000));
			@mkdir($folder);
			chmod($folder, 755);
			$filename = $folder . '/' . $book->id . '.jpg';
			$upload = new UploadAvatar($_FILES['cover']['tmp_name'], 100, 100, "simple", $filename);
			if ($upload->out)
				$to_update['is_cover'] = 1;
			else {
				throw new Exception('cant copy file to ' . $filename, 100);
			}
		}

		if (isset($_FILES['file']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name']) {
			$filetype_ = explode('.', $_FILES['file']['name']);
			$filetype_ = isset($filetype_[count($filetype_) - 1]) ? $filetype_[count($filetype_) - 1] : '';
			$fts = Config::need('filetypes');
			$filetype = false;
			foreach ($fts as $ftid => $ftname)
				if ($ftname == $filetype_)
					$filetype = $ftid;
				else
					echo $ftname;

			if (!$filetype)
				throw new Exception('wrong filetype:' . $filetype_);

			$destinationDir = Config::need('files_path') . DIRECTORY_SEPARATOR . getBookFileDirectory($book->id, $filetype);
			@mkdir($destinationDir, 755);
			// добавляем запись в базу
			$filesize = $_FILES['file']['size'];
			$query = 'SELECT * FROM `book_files` WHERE `id_book`=' . $book->id;
			$files = Database::sql2array($query, 'filetype');
			if (isset($files[$filetype])) {
				$old_id_file = $files[$filetype]['id'];
				$old_id_file_author = $files[$filetype]['id_file_author'];
				$old_filesize = $files[$filetype]['filesize'];
				$query = 'DELETE FROM `book_files` WHERE `id`=' . $old_id_file;
				Database::query($query);

				$query = 'INSERT IGNORE INTO `book_files` SET
				`id_book`=' . $book->id . ',
				`filetype`=' . $filetype . ',
				`id_file_author`=' . $current_user->id . ',
				`modify_time`=' . time() . ',
				`filesize`=' . $filesize;
				Database::query($query);
				$id_file = Database::lastInsertId();
				BookLog::addLog(array('id_file' => $id_file, 'filetype' => $filetype, 'id_file_author' => $current_user->id, 'filesize' => $filesize), array('id_file' => $old_id_file, 'filetype' => 0, 'id_file_author' => $old_id_file_author, 'filesize' => $old_filesize), $book->id);
				Database::query($query);
			} else {
				$query = 'INSERT INTO `book_files` SET
				`id_book`=' . $book->id . ',
				`filetype`=' . $filetype . ',
				`id_file_author`=' . $current_user->id . ',
				`modify_time`=' . time() . ',
				`filesize`=' . $filesize;
				Database::query($query);
				$id_file = Database::lastInsertId();
				BookLog::addLog(array('id_file' => $id_file, 'filetype' => $filetype, 'id_file_author' => $current_user->id, 'filesize' => $filesize), array('id_file' => 0, 'filetype' => 0, 'id_file_author' => 0, 'filesize' => 0), $book->id);
			}
			if ($id_file) {
				$destinationFile = getBookFilePath($id_file, $book->id, $filetype, Config::need('files_path'));
				if (!move_uploaded_file($_FILES['file']['tmp_name'], $destinationFile))
					throw new Exception('Cant save file to ' . $destinationFile);

				if ($filetype == 1) { // FB2
					$parser = new FB2Parser($destinationFile);
					$parser->parseDescription();
					$toc = $parser->getTOCHTML();
					Request::$post['annotation'] = $parser->getProperty('annotation');
					Request::$post['title'] = $parser->getProperty('book-title');
					$to_update['table_of_contents'] = $toc;
				}
			}
		}


		foreach ($fields as $field => $bookfield) {
			if (!isset(Request::$post[$field])) {
				throw new Exception('field missed #[' . $field.']');
			}
			if ($book->data[$bookfield] !== Request::$post[$field]) {
				$to_update[$bookfield] = Request::$post[$field];
			}
		}

		$q = array();


		foreach ($to_update as $field => &$value) {
			if (in_array($field, array('ISBN', 'year'))) {
				$value = is_numeric($value) ? $value : 0;
			}
			$q[] = '`' . $field . '`=' . Database::escape($value) . '';
		}

		if (count($q)) {
			$query = 'UPDATE `book` SET ' . implode(',', $q) . ' WHERE `id`=' . $book->id;
			Database::query($query);
			BookLog::addLog($to_update, $book->data, $book->id);
		}
		BookLog::saveLog($book->id, BookLog::TargetType_book, $current_user->id, BiberLog::BiberLogType_bookEdit);
		Books::getInstance()->dropCache($book->id);
		ob_end_clean();
		header('Location:' . Config::need('www_path') . '/b/' . $book->id);
		Database::query('COMMIT');
		exit();
	}

}