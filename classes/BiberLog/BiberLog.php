<?php

class BiberLog {
	const BiberLogType_bookEdit = 1;
	const BiberLogType_bookNew = 2;
	//
	const BiberLogType_personEdit = 5;
	const BiberLogType_personNew = 6;
	//
	const BiberLogType_magazineEdit = 9;
	const BiberLogType_magazineNew = 10;
	//
	const BiberLogType_serieEdit = 13;
	const BiberLogType_serieNew = 14;
	//
	const BiberLogType_personSetDuplicate = 50;
	const BiberLogType_personSetNoDuplicate = 51;
	const BiberLogType_personAddRelation = 52;
	const BiberLogType_personDelRelation = 53;
	//
	const BiberLogType_bookEditPerson = 101;
	const BiberLogType_bookEditGenre = 102;
	const BiberLogType_bookEditSerie = 103;
	//
	const BiberLogType_bookEditFile = 201;
	//
	const BiberLogType_bookSetDuplicate = 210;
	const BiberLogType_bookSetNoDuplicate = 211;
	const BiberLogType_bookAddRelation = 213;
	const BiberLogType_bookDelRelation = 214;
	//
	const TargetType_book = 1;
	const TargetType_person = 2;
	const TargetType_magazine = 3;
	const TargetType_serie = 4;
	//


	public static $changed = array();
	public static $id = array();
	public static $actionTypes = array(
	    self::BiberLogType_bookEdit => 'book_edit', //ok
	    self::BiberLogType_bookEditPerson => 'book_edit_person', //ok
	    self::BiberLogType_bookEditGenre => 'book_edit_genre', // ok
	    self::BiberLogType_bookEditSerie => 'book_edit_serie', // ok
	    self::BiberLogType_bookEditFile => 'book_edit_file', //later
	    self::BiberLogType_bookNew => 'book_new', // later
	    self::BiberLogType_personEdit => 'author_edit', // ok
	    self::BiberLogType_personNew => 'author_new', // later
	    self::BiberLogType_magazineEdit => 'magazine_edit', // ok
	    self::BiberLogType_magazineNew => 'magazine_new', // later
	    // book relations
	    self::BiberLogType_bookSetDuplicate => 'boook_is_duplicate', // ok
	    self::BiberLogType_bookSetNoDuplicate => 'book_is_no_duplicate', // ok
	    self::BiberLogType_bookAddRelation => 'book_new_relations', // ok
	    self::BiberLogType_bookDelRelation => 'book_delete_relations', // ok
	    // person relations
	    self::BiberLogType_personSetDuplicate => 'author_is_duplicate', // ok
	    self::BiberLogType_personSetNoDuplicate => 'author_is_no_duplicate', // ok
	    self::BiberLogType_personAddRelation => 'author_new_relations', // ok
	    self::BiberLogType_personDelRelation => 'author_delete_relations', // ok
	    //series
	    self::BiberLogType_serieEdit => 'serie_edit', // ok
	    self::BiberLogType_serieNew => 'serie_new', //later
	);

	public static function setChangedField($fieldtype, $oldValue, $newValue) {
		self::$changed[$fieldtype] = array($oldValue, $newValue);
	}

	public static function setIdField($name, $value) {
		self::$id[$name] = $value;
	}

	public static function saveLogLink($id_log, $id_target, $target_type, $id_user, $action_type, $copy = false) {
		if (!is_array($id_target))
			$id_target = array($id_target);

		$time = time();
		$i = 0;
		foreach ($id_target as $id) {
			$is_copy = 0;
			if ($copy || ($i > 0))
				$is_copy = 1;
			$i++;
			$query = 'INSERT IGNORE INTO `biber_log_index` SET
            `id_target`=' . $id . ',
            `target_type`=' . $target_type . ',
	    `id_log` =' . $id_log . ',
            `id_user`=' . $id_user . ',
	    `is_copy`=' . $is_copy . ',
            `time`=' . $time;
			Database::query($query);
		}
		return $id_log;
	}

	public static function saveLog($id_target, $target_type, $id_user, $action_type, $copy = false) {
		if (!is_array($id_target))
			$id_target = array($id_target);
		if (!count(self::$changed))
			return;
		foreach (self::$id as $name => $value)
			self::$changed[$name] = $value;
		if (!count(self::$id))
			throw new Exception('No id for save Log');
		$data = serialize(self::$changed);
		$time = time();
		$query = 'INSERT INTO `biber_log` SET
            `action_type`=' . $action_type . ',
            `id_user`=' . $id_user . ',
            `time`=' . $time . ',
            `data`=' . Database::escape($data);
		Database::query($query);
		$lid = Database::lastInsertId();
		$i = 0;
		foreach ($id_target as $id) {
			$is_copy = 0;
			if ($copy || ($i > 0))
				$is_copy = 1;
			$i++;
			$query = 'INSERT IGNORE INTO `biber_log_index` SET
            `id_target`=' . $id . ',
            `target_type`=' . $target_type . ',
	    `id_log` =' . $lid . ',
            `id_user`=' . $id_user . ',
	    `is_copy`=' . $is_copy . ',
            `time`=' . $time;
			Database::query($query);
		}
		return $lid;
	}

	private static function restoreBookFile($id_book, $id_file_to_restore, $id_user_to_restore, $filesize_to_restore, $filetype) {
		global $current_user;
		// delete old file
		$query = 'DELETE FROM `book_files` WHERE `id_book`=' . $id_book . ' AND `filetype`=' . $filetype;
		Database::query($query);
		// apply new file
		if ($id_file_to_restore) {
			$query = 'INSERT INTO `book_files` SET `id_book`=' . $id_book . ' , `filetype`=' . $filetype . ', `id`=' . $id_file_to_restore . ',
			`id_file_author`=' . $id_user_to_restore . ',
			`modify_time`=' . time() . ',
			`filesize`=' . $filesize_to_restore;
			Database::query($query);
		}
	}

	private static function undo_undo_bookEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (in_array($field, array('id_file', 'id_file_author', 'filesize', 'filetype'))) {
				self::restoreBookFile($data['id_book'], $data['id_file'][0], $data['id_file_author'][0], $data['filesize'][0], $data['filetype'][1]);
				continue;
			}
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[0]);
		}
		if (count($q)) {
			$query = 'UPDATE `book` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_book'];
			Database::query($query);
		}
	}

	private static function undo_repeat_bookEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (in_array($field, array('id_file', 'id_file_author', 'filesize', 'filetype'))) {
				self::restoreBookFile($data['id_book'], $data['id_file'][1], $data['id_file_author'][1], $data['filesize'][1], $data['filetype'][1]);
				continue;
			}
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[1]);
		}
		if (count($q)) {
			$query = 'UPDATE `book` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_book'];
			Database::query($query);
		}
	}

	private static function undo_undo_personEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[0]);
		}
		if (count($q)) {
			$query = 'UPDATE `persons` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_person'];
			Database::query($query);
		}
	}

	private static function undo_repeat_personEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[1]);
		}
		if (count($q)) {
			$query = 'UPDATE `persons` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_person'];
			Database::query($query);
		}
	}

	private static function undo_undo_serieEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[0]);
		}
		if (count($q)) {
			$query = 'UPDATE `series` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_serie'];
			Database::query($query);
		}
	}

	private static function undo_repeat_serieEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[1]);
		}
		if (count($q)) {
			$query = 'UPDATE `series` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_serie'];
			Database::query($query);
		}
	}

	private static function undo_undo_magazineEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[0]);
		}
		if (count($q)) {
			$query = 'UPDATE `magazines` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_magazine'];
			Database::query($query);
		}
	}

	private static function undo_repeat_magazineEdit($logdata) {
		$data = unserialize($logdata['data']);
		$q = array();
		foreach ($data as $field => $value) {
			if (is_array($value))
				$q[] = '`' . $field . '`=' . Database::escape($value[1]);
		}
		if (count($q)) {
			$query = 'UPDATE `magazines` SET ' . implode(',', $q) . ' WHERE `id`=' . $data['id_magazine'];
			Database::query($query);
		}
	}

	// отменяем удаление/ добавление автора книги
	private static function undo_undo_bookEditPerson($logdata) {
		$data = unserialize($logdata['data']);
		if ($data['id_person'][0]) {
			// удалили автора, надо добавить
			$query = 'INSERT INTO `book_persons` SET
				`id_book`=' . $data['id_book'] . ',
				`id_person`=' . $data['id_person'][0] . ',
				`person_role`=' . $data['person_role'][0] . '
					ON DUPLICATE KEY UPDATE
				`id_book`=' . $data['id_book'] . ',
				`id_person`=' . $data['id_person'][0] . ',
				`person_role`=' . $data['person_role'][0];
		} else {
			// добавили автора, надо удалить
			$query = 'DELETE FROM `book_persons` WHERE `id_book`=' . $data['id_book'] . ' AND `id_person`=' . $data['id_person'][1];
		}
		Database::query($query);
	}

	// еще раз добавляем / удаляем автора книги
	private static function undo_repeat_bookEditPerson($logdata) {
		$data = unserialize($logdata['data']);

		if ($data['id_person'][0]) {
			$query = 'DELETE FROM `book_persons` WHERE `id_book`=' . $data['id_book'] . ' AND `id_person`=' . $data['id_person'][0];
			// удалили автора, надо повторить
		} else {
			// добавили автора, надо повторить
			$query = 'INSERT INTO `book_persons` SET
				`id_book`=' . $data['id_book'] . ',
				`id_person`=' . $data['id_person'][1] . ',
				`person_role`=' . $data['person_role'][1] . '
					ON DUPLICATE KEY UPDATE
				`id_book`=' . $data['id_book'] . ',
				`id_person`=' . $data['id_person'][1] . ',
				`person_role`=' . $data['person_role'][1];
		}
		Database::query($query);
	}

	// отменяем удаление/ добавление жанра книги
	private static function undo_undo_bookEditGenre($logdata) {
		$data = unserialize($logdata['data']);
		if ($data['id_genre'][0]) {

			$query = 'INSERT INTO `book_genre` SET
				`id_book`=' . $data['id_book'] . ',
				`id_genre`=' . $data['id_genre'][0] . '
					ON DUPLICATE KEY UPDATE
				`id_book`=' . $data['id_book'] . ',
				`id_genre`=' . $data['id_genre'][0];
		} else {
			$query = 'DELETE FROM `book_genre` WHERE `id_book`=' . $data['id_book'] . ' AND `id_genre`=' . $data['id_genre'][1];
		}
		Database::query($query);
	}

	// еще раз добавляем / удаляем жанр книги
	private static function undo_repeat_bookEditGenre($logdata) {
		$data = unserialize($logdata['data']);
		if ($data['id_genre'][0]) {

			$query = 'DELETE FROM `book_genre` WHERE `id_book`=' . $data['id_book'] . ' AND `id_genre`=' . $data['id_genre'][0];
		} else {
			$query = 'INSERT INTO `book_genre` SET
				`id_book`=' . $data['id_book'] . ',
				`id_genre`=' . $data['id_genre'][1] . '
					ON DUPLICATE KEY UPDATE
				`id_book`=' . $data['id_book'] . ',
				`id_genre`=' . $data['id_genre'][1];
		}
		Database::query($query);
	}

	// отменяем удаление/ добавление серии книги
	private static function undo_undo_bookEditSerie($logdata) {
		$data = unserialize($logdata['data']);
		$book_id = $data['id_book'];
		if ($data['id_serie'][0]) {

			$query = 'INSERT INTO `book_series` SET
				`id_book`=' . $book_id . ',
				`id_series`=' . $data['id_serie'][0] . '
					ON DUPLICATE KEY UPDATE
				`id_book`=' . $book_id . ',
				`id_series`=' . $data['id_serie'][0];
		} else {
			$query = 'DELETE FROM `book_series` WHERE `id_book`=' . $book_id . ' AND `id_series`=' . $data['id_serie'][1];
		}
		Database::query($query);
	}

	// еще раз добавляем / удаляем серию книги
	private static function undo_repeat_bookEditSerie($logdata) {
		$data = unserialize($logdata['data']);
		$book_id = $data['id_book'];
		if ($data['id_serie'][0]) {
			$query = 'DELETE FROM `book_series` WHERE `id_book`=' . $book_id . ' AND `id_series`=' . $data['id_serie'][0];
		} else {
			$query = 'INSERT INTO `book_series` SET
				`id_book`=' . $book_id . ',
				`id_series`=' . $data['id_serie'][1] . '
					ON DUPLICATE KEY UPDATE
				`id_book`=' . $book_id . ',
				`id_series`=' . $data['id_serie'][1];
		}
		Database::query($query);
	}

	// Отменям действие по добавлению связей книг
	private static function undo_undo_bookAddRelation($logdata) {
		$data = unserialize($logdata['data']);
		$new_ids = $data['new_relations'][1];
		$books_to_delete_from_basket = Books::getInstance()->getByIdsLoaded($new_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были добавленные книги
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих книг
		foreach ($books_to_delete_from_basket as $book) {
			/* @var $book Book */
			if ($book->getBasketId() == $old_basket) {
				unset($new_ids[$book->id]);
			}
		}
		// чистим корзину
		if (count($new_ids)) {
			if ($old_basket)
				$query = 'UPDATE `book_basket` SET `id_basket`=' . $old_basket . ' WHERE `id_book` IN (' . implode(',', $new_ids) . ')';
			else
				$query = 'DELETE FROM `book_basket` WHERE `id_book` IN (' . implode(',', $new_ids) . ')';
			Database::query($query);

			// апдейтим книжки
			$query = 'UPDATE `book` SET `id_basket`=' . $old_basket . ' WHERE `id` IN (' . implode(',', $new_ids) . ')';
			Database::query($query);
		}
	}

	// Повторяем действие по добавлению связей книг
	private static function undo_repeat_bookAddRelation($logdata) {
		$data = unserialize($logdata['data']);
		$new_ids = $data['new_relations'][1];
		$books_to_delete_from_basket = Books::getInstance()->getByIdsLoaded($new_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были добавленные книги
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих книг
		// удаляем из корзин эти книги
		if (count($new_ids)) {
			$query = 'DELETE FROM `book_basket`  WHERE `id_book` IN (' . implode(',', $new_ids) . ')';
			Database::query($query);
		}
		// в корзину $new_basket добавляем все книжки
		$q = array();
		foreach ($books_to_delete_from_basket as $book) {
			/* @var $book Book */
			$q[] = '(' . $new_basket . ',' . $book->id . ')';
		}
		$query = 'REPLACE INTO `book_basket` VALUES ' . implode(',', $q);
		Database::query($query);
		// апдейтим книжки
		$query = 'UPDATE `book` SET `id_basket`=' . $new_basket . ' WHERE `id` IN (' . implode(',', $new_ids) . ')';
		Database::query($query);
	}

	// Отменям действие по добавлению связей книг
	private static function undo_undo_personAddRelation($logdata) {
		$data = unserialize($logdata['data']);
		$new_ids = $data['new_relations'][1];
		$persons_to_delete_from_basket = Persons::getInstance()->getByIdsLoaded($new_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были добавленные авторы
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих авторов
		foreach ($persons_to_delete_from_basket as $person) {
			/* @var $person Person */
			if ($person->getBasketId() == $old_basket) {
				unset($new_ids[$book->id]);
			}
		}
		// чистим корзину
		if (count($new_ids)) {
			if ($old_basket)
				$query = 'UPDATE `person_basket` SET `id_basket`=' . $old_basket . ' WHERE `id_person` IN (' . implode(',', $new_ids) . ')';
			else
				$query = 'DELETE FROM `person_basket` WHERE `id_person` IN (' . implode(',', $new_ids) . ')';
			Database::query($query);

			// апдейтим книжки
			$query = 'UPDATE `persons` SET `id_basket`=' . $old_basket . ' WHERE `id` IN (' . implode(',', $new_ids) . ')';
			Database::query($query);
		}
	}

	// Повторяем действие по добавлению связей книг
	private static function undo_repeat_personAddRelation($logdata) {
		$data = unserialize($logdata['data']);
		$new_ids = $data['new_relations'][1];
		$persons_to_delete_from_basket = Persons::getInstance()->getByIdsLoaded($new_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были добавленные авторы
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих авторов
		// удаляем из корзин эти книги
		if (count($new_ids)) {
			$query = 'DELETE FROM `person_basket`  WHERE `id_person` IN (' . implode(',', $new_ids) . ')';
			Database::query($query);
		}
		// в корзину $new_basket добавляем всех авторов
		$q = array();
		foreach ($persons_to_delete_from_basket as $person) {
			/* @var $person Person */
			$q[] = '(' . $new_basket . ',' . $person->id . ')';
		}
		$query = 'REPLACE INTO `person_basket` VALUES ' . implode(',', $q);
		Database::query($query);
		// апдейтим книжки
		$query = 'UPDATE `persons` SET `id_basket`=' . $new_basket . ' WHERE `id` IN (' . implode(',', $new_ids) . ')';
		Database::query($query);
	}

	// Отменяем действие по удалению связей книг
	private static function undo_undo_bookDelRelation($logdata) {
		$data = unserialize($logdata['data']);
		$deleted_ids = $data['deleted_relations'][1];
		$books_to_insert_in_basket = Books::getInstance()->getByIdsLoaded($deleted_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были удаленные книги
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих книг
		// удаляем из корзин эти книги
		if (count($deleted_ids)) {
			$query = 'DELETE FROM `book_basket`  WHERE `id_book` IN (' . implode(',', $deleted_ids) . ')';
			Database::query($query);
		}
		// в корзину $old_basket добавляем все книжки
		$q = array();
		foreach ($books_to_insert_in_basket as $book) {
			/* @var $book Book */
			$q[] = '(' . $old_basket . ',' . $book->id . ')';
		}
		$query = 'REPLACE INTO `book_basket` VALUES ' . implode(',', $q);
		Database::query($query);
		// апдейтим книжки
		$query = 'UPDATE `book` SET `id_basket`=' . $old_basket . ' WHERE `id` IN (' . implode(',', $deleted_ids) . ')';
		Database::query($query);
	}

	// Повторяем действие по удалению связей книг
	private static function undo_repeat_bookDelRelation($logdata) {
		$data = unserialize($logdata['data']);
		$deleted_ids = $data['deleted_relations'][1];
		$books_to_insert_in_basket = Books::getInstance()->getByIdsLoaded($deleted_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были удаленные книги
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих книг
		// удаляем из корзин эти книги
		if (count($deleted_ids)) {
			$query = 'DELETE FROM `book_basket`  WHERE `id_book` IN (' . implode(',', $deleted_ids) . ')';
			Database::query($query);
		}
		// апдейтим книжки
		$query = 'UPDATE `book` SET `id_basket`=0 WHERE `id` IN (' . implode(',', $deleted_ids) . ')';
		Database::query($query);
	}

	// Отменяем действие по удалению связей книг
	private static function undo_undo_personDelRelation($logdata) {
		$data = unserialize($logdata['data']);
		$deleted_ids = $data['deleted_relations'][1];
		$persons_to_insert_in_basket = Persons::getInstance()->getByIdsLoaded($deleted_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были удаленные книги
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих книг
		// удаляем из корзин эти книги
		if (count($deleted_ids)) {
			$query = 'DELETE FROM `person_basket`  WHERE `id_person` IN (' . implode(',', $deleted_ids) . ')';
			Database::query($query);
		}
		// в корзину $old_basket добавляем всех авторов
		$q = array();
		foreach ($persons_to_insert_in_basket as $person) {
			/* @var $person Person */
			$q[] = '(' . $old_basket . ',' . $person->id . ')';
		}
		$query = 'REPLACE INTO `person_basket` VALUES ' . implode(',', $q);
		Database::query($query);
		// апдейтим книжки
		$query = 'UPDATE `persons` SET `id_basket`=' . $old_basket . ' WHERE `id` IN (' . implode(',', $deleted_ids) . ')';
		Database::query($query);
	}

	// Повторяем действие по удалению связей книг
	private static function undo_repeat_personDelRelation($logdata) {
		$data = unserialize($logdata['data']);
		$deleted_ids = $data['deleted_relations'][1];
		$persons_to_insert_in_basket = Books::getInstance()->getByIdsLoaded($deleted_ids);
		$old_basket = $data['id_basket'][0]; // в этой корзине были удаленные авторы
		$new_basket = $data['id_basket'][1]; // а такая корзина стала у этих авторов
		// удаляем из корзин этих авторов
		if (count($deleted_ids)) {
			$query = 'DELETE FROM `person_basket`  WHERE `id_person` IN (' . implode(',', $deleted_ids) . ')';
			Database::query($query);
		}
		// апдейтим книжки
		$query = 'UPDATE `persons` SET `id_basket`=0 WHERE `id` IN (' . implode(',', $deleted_ids) . ')';
		Database::query($query);
	}

	private static function undo_undo_bookSetDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `book` SET `is_duplicate`=0 WHERE `id`=' . $data['id_book'];
		Database::query($query);
	}

	private static function undo_repeat_bookSetDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `book` SET `is_duplicate`=' . ((int) $data['is_duplicate'][1]) . ' WHERE `id`=' . $data['id_book'];
		Database::query($query);
	}

	private static function undo_undo_bookSetNoDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `book` SET `is_duplicate`=' . ((int) $data['is_duplicate'][0]) . ' WHERE `id`=' . $data['id_book'];
		Database::query($query);
	}

	private static function undo_repeat_bookSetNoDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `book` SET `is_duplicate`=0 WHERE `id`=' . $data['id_book'];
		Database::query($query);
	}

	private static function undo_undo_authorSetDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `persons` SET `is_p_duplicate`=0 WHERE `id`=' . $data['id_person'];
		Database::query($query);
	}

	private static function undo_repeat_authorSetDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `persons` SET `is_p_duplicate`=' . ((int) $data['is_p_duplicate'][1]) . ' WHERE `id`=' . $data['id_person'];
		Database::query($query);
	}

	private static function undo_undo_authorSetNoDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `persons` SET `is_p_duplicate`=' . ((int) $data['is_p_duplicate'][0]) . ' WHERE `id`=' . $data['id_person'];
		Database::query($query);
	}

	private static function undo_repeat_authorSetNoDuplicate($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `persons` SET `is_p_duplicate`=0 WHERE `id`=' . $data['id_person'];
		Database::query($query);
	}

	private static function undo_undo_bookNew($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `book` SET `is_deleted`=1 WHERE `id`=' . $data['id_book'];
		Database::query($query);
	}

	private static function undo_repeat_bookNew($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `book` SET `is_deleted`=0 WHERE `id`=' . $data['id_book'];
		Database::query($query);
		self::undo_repeat_bookEdit($logdata);
	}
	
	private static function undo_undo_personNew($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `persons` SET `is_deleted`=1 WHERE `id`=' . $data['id_person'];
		Database::query($query);
	}

	private static function undo_repeat_personNew($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `persons` SET `is_deleted`=0 WHERE `id`=' . $data['id_person'];
		Database::query($query);
		self::undo_repeat_personEdit($logdata);
	}
	
	private static function undo_undo_serieNew($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `series` SET `is_deleted`=1 WHERE `id`=' . $data['id_serie'];
		Database::query($query);
	}

	private static function undo_repeat_serieNew($logdata) {
		$data = unserialize($logdata['data']);
		$query = 'UPDATE `series` SET `is_deleted`=0 WHERE `id`=' . $data['id_serie'];
		Database::query($query);
		self::undo_repeat_serieEdit($logdata);
	}

	// откатываем
	private static function undo_undo($logdata) {
		$query = 'UPDATE `biber_log` SET `undo`=1 WHERE `id`=' . $logdata['id'];
		Database::query($query);
		switch ($logdata['action_type']) {
			case BiberLog::BiberLogType_bookEdit:
				self::undo_undo_bookEdit($logdata);
				break;
			case BiberLog::BiberLogType_bookEditPerson:
				self::undo_undo_bookEditPerson($logdata);
				break;
			case BiberLog::BiberLogType_bookEditGenre:
				self::undo_undo_bookEditGenre($logdata);
				break;
			case BiberLog::BiberLogType_bookEditSerie:
				self::undo_undo_bookEditSerie($logdata);
				break;
			case BiberLog::BiberLogType_bookAddRelation:
				self::undo_undo_bookAddRelation($logdata);
				break;
			case BiberLog::BiberLogType_bookDelRelation:
				self::undo_undo_bookDelRelation($logdata);
				break;
			case BiberLog::BiberLogType_personAddRelation:
				self::undo_undo_personAddRelation($logdata);
				break;
			case BiberLog::BiberLogType_personDelRelation:
				self::undo_undo_personDelRelation($logdata);
				break;
			case BiberLog::BiberLogType_personEdit:
				self::undo_undo_personEdit($logdata);
				break;
			case BiberLog::BiberLogType_serieEdit:
				self::undo_undo_serieEdit($logdata);
				break;
			case BiberLog::BiberLogType_magazineEdit:
				self::undo_undo_magazineEdit($logdata);
				break;
			case BiberLog::BiberLogType_bookSetDuplicate:
				self::undo_undo_bookSetDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_bookSetNoDuplicate:
				self::undo_undo_bookSetNoDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_personSetDuplicate:
				self::undo_undo_authorSetDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_personSetNoDuplicate:
				self::undo_undo_authorSetNoDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_bookNew:
				self::undo_undo_bookNew($logdata);
				break;
			case BiberLog::BiberLogType_personNew:
				self::undo_undo_personNew($logdata);
				break;
			case BiberLog::BiberLogType_serieNew:
				self::undo_undo_serieNew($logdata);
				break;
			default:
				throw new Exception('log action #' . self::$actionTypes[$logdata['action_type']] . ' cant be undo yet');
				break;
		}
		return true;
	}

	// накатываем
	private static function undo_repeat($logdata) {
		$query = 'UPDATE `biber_log` SET `undo`=0 WHERE `id`=' . $logdata['id'];
		Database::query($query);
		switch ($logdata['action_type']) {
			case BiberLog::BiberLogType_bookEdit:
				self::undo_repeat_bookEdit($logdata);
				break;
			case BiberLog::BiberLogType_bookEditPerson:
				self::undo_repeat_bookEditPerson($logdata);
				break;
			case BiberLog::BiberLogType_bookEditGenre:
				self::undo_repeat_bookEditGenre($logdata);
				break;
			case BiberLog::BiberLogType_bookEditSerie:
				self::undo_repeat_bookEditSerie($logdata);
				break;
			case BiberLog::BiberLogType_bookAddRelation:
				self::undo_repeat_bookAddRelation($logdata);
				break;
			case BiberLog::BiberLogType_bookDelRelation:
				self::undo_repeat_bookDelRelation($logdata);
				break;
			case BiberLog::BiberLogType_personAddRelation:
				self::undo_repeat_personAddRelation($logdata);
				break;
			case BiberLog::BiberLogType_personDelRelation:
				self::undo_repeat_personDelRelation($logdata);
				break;
			case BiberLog::BiberLogType_personEdit:
				self::undo_repeat_personEdit($logdata);
				break;
			case BiberLog::BiberLogType_serieEdit:
				self::undo_repeat_serieEdit($logdata);
				break;
			case BiberLog::BiberLogType_magazineEdit:
				self::undo_repeat_magazineEdit($logdata);
				break;
			case BiberLog::BiberLogType_bookSetDuplicate:
				self::undo_repeat_bookSetDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_bookSetNoDuplicate:
				self::undo_repeat_bookSetNoDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_personSetDuplicate:
				self::undo_repeat_authorSetDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_personSetNoDuplicate:
				self::undo_repeat_authorSetNoDuplicate($logdata);
				break;
			case BiberLog::BiberLogType_bookNew:
				self::undo_repeat_bookNew($logdata);
				break;
			case BiberLog::BiberLogType_personNew:
				self::undo_repeat_personNew($logdata);
				break;
			case BiberLog::BiberLogType_serieNew:
				self::undo_repeat_serieNew($logdata);
				break;
			default:
				throw new Exception('log action #' . self::$actionTypes[$logdata['action_type']] . ' cant be repeat yet');
				break;
		}
		return true;
	}

	public static function undo($id, $data = false, $undo = true) {
		$id = max(0, (int) $id);
		if (!$id)
			throw new Exception('Illegal log id');
		if ($data) {
			$logdata = $data;
		} else {
			$query = 'SELECT * FROM `biber_log` WHERE `id`=' . $id;
			$logdata = Database::sql2row($query);
		}
		if (!$logdata || !isset($logdata['id']))
			throw new Exception('Illegal log id');
		Database::query('START TRANSACTION');
		switch ((int) $logdata['undo'] === 0) {
			case true:
				if (!$undo)
					self::undo_undo($logdata);
				else
					throw new Exception('Не могу повторить действие #' . $logdata['id'] . ' (уже повторили)');
				break;
			default:
				if ($undo)
					self::undo_repeat($logdata);
				else
					throw new Exception('Не могу отменить действие #' . $logdata['id'] . ' (уже отменено)');
				break;
		}
		Database::query('COMMIT');
		return true;
	}

}