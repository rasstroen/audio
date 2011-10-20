<?php

class Book extends BaseObjectClass {

	public $id;
	public $persons;
	public $genres;
	public $series;
	public $rightsholder;
	public $files;
	public $reviews;
	public $reviewsLoaded;
	public $personsLoaded = false;
	public $genresLoaded = false;
	public $seriesLoaded = false;
	public $loaded = false;
	public $rightsholderLoaded = false;
	public $filesLoaded = false;
	public $data;

	const BOOK_TYPE_BOOK = 1;
	const BOOK_TYPE_MAGAZINE = 2;
	public static $book_types = array(
	    self::BOOK_TYPE_BOOK => 'book',
	    self::BOOK_TYPE_MAGAZINE => 'magazine'
	);

	const ROLE_AUTHOR = 1; // автор
	const ROLE_TRANSL = 2; // переводчик
	const ROLE_ABOUTA = 3; // об авторе
	const ROLE_ILLUST = 4; // иллюстратор
	const ROLE_SOSTAV = 5; // составитель
	const ROLE_DIRECT = 6; // директор
	const ROLE_OFORMI = 7; // оформитель
	const ROLE_EDITOR = 8; // редактор
	//
	const REVIEW_TYPE_BOOK = 0;

	function __construct($id, $data = false) {
		$this->id = $id;
		if ($data) {
			if ($data == 'empty') {
				$this->loaded = true;
				$this->personsLoaded = true;
				$this->exists = false;
			}
			$this->load($data);
		}
	}

	function _show() {
		$out = array();
		if ($this->loadForFullView()) {
			if ($redirect_to = $this->getDuplicateId()) {
				$book2 = Books::getInstance()->getByIdLoaded($redirect_to);
				if ($book2->loaded) {
					@ob_end_clean();
					header('Location: ' . $this->getUrl($redirect = true));
					exit();
				}
			}
			$out['id'] = $this->id;
			$langId = $this->data['id_lang'];
			foreach (Config::$langs as $code => $id_lang) {
				if ($id_lang == $langId) {
					$langCode = $code;
				}
			}
			$out['quality'] = $this->getQuality();
			$out['lang_code'] = $langCode;
			$out['lang_title'] = Config::$langRus[$langCode];
			$out['lang_id'] = $langId;
			$title = $this->getTitle();

			$out['title'] = $title['title'];
			$out['subtitle'] = $title['subtitle'];

			$out['public'] = $this->isPublic();
			$out['qualities'] = array(
			    0 => array('id' => 0, 'title' => 'не оценен'),
			    1 => array('id' => 1, 'title' => 'ужасно'),
			    2 => array('id' => 2, 'title' => 'плохо'),
			    3 => array('id' => 3, 'title' => 'средне'),
			    4 => array('id' => 4, 'title' => 'хорошо'),
			    5 => array('id' => 5, 'title' => 'идеально'),
			);
			$persons = $this->getPersons();
			uasort($persons, 'sort_by_role');
			foreach ($persons as $data) {
				$tmp_person = Persons::getInstance()->getById($data['id'], $data);
				if ($tmp_person->id) {
					$out['authors'][$data['id']] = $tmp_person->getListData();
					$out['authors'][$data['id']]['role'] = $data['role'];
					$out['authors'][$data['id']]['roleName'] = $data['roleName'];
				}
			}

			$out['genres'] = $this->getGenres();
			$out['series'] = $this->getSeries();
			$out['isbn'] = $this->getISBN();
			$out['rightsholder'] = $this->getRightsholder();
			$out['annotation'] = $this->getAnnotation();
			$out['cover'] = $this->getCover();
			$out['files'] = $this->getFiles();

			$out['mark'] = $this->getMarkNumber();
			$out['mark_percents'] = $this->getMarkPercents();
			$out['mark_number'] = $this->getMarkRoundNumber();
			$out['path'] = $this->getUrl();
			$out['lastSave'] = $this->data['modify_time'];

			$out['year'] = (int) $this->data['year'] ? (int) $this->data['year'] : '';
			$out['book_type'] = Book::$book_types[$this->data['book_type']];
		}
		else
			throw new Exception('no book #' . $id . ' in database');
		return $out;
	}

	function getDuplicateId() {
		if (!$this->loaded) {
			$this->load();
		}
		return (int) $this->data['is_duplicate'];
	}

	function getLangId() {
		if (!$this->loaded) {
			$this->load();
		}
		return (int) $this->data['id_lang'];
	}

	function getBasketId() {
		if (!$this->loaded) {
			$this->load();
		}
		return (int) $this->data['id_basket'];
	}

	function getUrl($redirect = false) {
		$id = $redirect ? $this->getDuplicateId() : $this->id;
		return Config::need('www_path') . '/book/' . $id;
	}

	// грузим некоторые данные книги одним запросом
	function loadForFullView() {
		// book
		// book_genre
		// genre
		// book_persons
		// persons
		$this->genres = array();
		$query = 'SELECT
			B.*,
			\'separator_genre\' as `separator_genre`,
			G.`id` as `id_genre`, G.`title` as `g_title`, G.`id_parent` as `gid_parent`, G.`name` as `gname`,
			\'separator_person\' as `separator_person`,
			P.`id` as `person_id`, P.*,
			\'separator_end\' as `separator_end`,
			BP.`person_role` as `person_role`
			FROM `book` B
			LEFT JOIN `book_genre` BG ON BG.id_book = B.id
			LEFT JOIN `genre` G ON G.id = BG.id_genre
			LEFT JOIN `book_persons` BP ON BP.id_book = B.id
			LEFT JOIN `persons` P ON P.id = BP.id_person
			WHERE B.id=' . $this->id . '
		';

		$res = Database::sql2array($query);

		if (!count($res))
			return false;
		$fieldsfor = 'book';
		foreach ($res as $row) {
			if (!$this->loaded) {
				foreach ($row as $f => $v) {
					if ($f == 'separator_book') {
						$fieldsfor = 'genre';
						continue;
					} else
					if ($f == 'separator_genre') {
						$fieldsfor = 'genre';
						continue;
					} else
					if ($f == 'separator_person') {
						$fieldsfor = 'person';
						continue;
					} else
					if ($f == 'separator_end') {
						$fieldsfor = false;
						continue;
					}

					if ($fieldsfor == 'book')
						$this->data[$f] = $v;
					else
					if ($fieldsfor == 'person') {
						if ($row['person_id']) {
							if ($f == 'person_id') {
								$f = 'id';
								$this->persons[$v]['role'] = $row['person_role'];
								$this->persons[$v]['roleName'] = $this->getPersonRoleName($row['person_role']);
							}
							$this->persons[$row['person_id']][$f] = $v;
						}
					} else
					if ($fieldsfor == 'genre') {
						if ($f == 'g_title')
							$f = 'title';
						if ($f == 'gid_parent')
							$f = 'id_parent';
						if ($f == 'id_genre')
							$f = 'id';
						if ($f == 'gname')
							$f = 'name';
						if ($row['id_genre']) {
							$this->genres[$row['id_genre']]['path'] = Config::need('www_path') . '/genres/' . $row['gname'];
							$this->genres[$row['id_genre']][$f] = $v;
						}
					}
				}
			}
		}
		if (is_array($this->persons))
			$this->persons = array_values($this->persons);
		else
			$this->persons = array();

		// одним запросом несколько лоадов
		$this->loaded = true;
		$this->genresLoaded = true;
		$this->personsLoaded = true;
		return true;
	}

	function load($data = false) {
		if ($this->loaded)
			return false;
		if (!$data) {
			$query = 'SELECT * FROM `book` WHERE `id`=' . $this->id;
			$this->data = Database::sql2row($query);
		}else
			$this->data = $data;
		$this->exists = true;
		$this->loaded = true;
	}

	function loadGenres() {
		if ($this->genresLoaded)
			return false;
		$query = 'SELECT `id_genre` FROM `book_genre` WHERE `id_book`=' . $this->id;
		$genres = Database::sql2array($query, 'id_genre');

		$ids = array_keys($genres);
		if (count($ids)) {
			$query = 'SELECT * FROM `genre` WHERE `id` IN (' . implode(',', $ids) . ')';
			$genresLib = Database::sql2array($query, 'id');

			foreach ($genres as $gen) {
				if (isset($genresLib[$gen['id_genre']])) {
					if ($genresLib[$gen['id_genre']]['id']) {
						$this->genres[$gen['id_genre']] = $genresLib[$gen['id_genre']];
						$this->genres[$gen['id_genre']]['path'] = Config::need('www_path') . '/genres/' . $genresLib[$gen['name']];
					}
				}
			}
		}else
			$this->genres = array();
		$this->genresLoaded = true;
	}

	function loadSeries() {
		if ($this->seriesLoaded)
			return false;
		$query = 'SELECT `id_series` FROM `book_series` WHERE `id_book`=' . $this->id;
		$series = Database::sql2array($query, 'id_series');

		$ids = array_keys($series);
		if (count($ids)) {
			$query = 'SELECT * FROM `series` WHERE `id` IN (' . implode(',', $ids) . ')';
			$seriesLib = Database::sql2array($query, 'id');

			foreach ($series as $ser) {
				$this->series[$ser['id_series']] = $seriesLib[$ser['id_series']];
				$this->series[$ser['id_series']]['path'] = Config::need('www_path') . '/series/' . $ser['id_series'];
			}
		}else
			$this->series = array();
		$this->seriesLoaded = true;
	}

	function loadReviews() {
		if ($this->reviewsLoaded)
			return false;
		$query = 'SELECT `id_user`,`comment`,`time`,`rate` FROM `reviews` WHERE `id_target`=' . $this->id . ' AND `target_type`=' . self::REVIEW_TYPE_BOOK;
		$reviews = Database::sql2array($query);
		$uids = array();
		foreach ($reviews as $review) {
			$uids[] = $review['id_user'];
		}
		if ($uids)
			$users = Users::getByIdsLoaded($uids);

		global $current_user;
		/* @var $current_user CurrentUser */
		foreach ($reviews as &$review) {
			if (isset($users[$review['id_user']])) {
				$review['nickname'] = $users[$review['id_user']]->getProperty('nickname', 'аноним');
				$review['picture'] = $users[$review['id_user']]->getProperty('picture') ? $users[$review['id_user']]->id . '.jpg' : 'default.jpg';
			} else {
				$review['nickname'] = 'аноним';
				$review['picture'] = 'default.jpg';
			}
		}
		$this->reviews = $reviews;
		$this->reviewsLoaded = true;
	}

	function loadRightsholder() {
		if (!$this->loaded) {
			$this->load();
		}
		if ($this->rightsholderLoaded) {
			return false;
		}
		if ($this->data['id_rightholder']) {
			$query = 'SELECT * FROM `rightholders` WHERE `id`=' . $this->data['id_rightholder'];
			$this->rightsholder = Database::sql2row($query);
			if (!is_array($this->rightsholder))
				$this->rightsholder = array();
		}else
			$this->rightsholder = array();


		$this->rightsholderLoaded = true;
	}

	function loadFiles() {
		if ($this->filesLoaded) {
			return false;
		}
		$query = 'SELECT * FROM `book_files` WHERE `id_book` = ' . $this->id;
		$res = Database::sql2array($query);
		$this->files = is_array($res) ? $res : array();
	}

	function loadPersons($persons = false) {
		if ($this->personsLoaded) {
			return false;
		}
		if (($persons !== false)) {
			
		} else {
			// связи
			$query = 'SELECT `person_role`,`id_person` FROM `book_persons` WHERE `id_book`=' . $this->id;
			$persons = Database::sql2array($query, 'id_person');
			// профили
			$personProfiles = array();
			if (count($persons)) {
				$ids = array_keys($persons);
				Persons::getInstance()->getByIdsLoaded($ids);
			}
		}

		foreach ($persons as $person) {
			$personItem = Persons::getInstance()->getByIdLoaded($person['id_person']);
			if ($personItem->loaded) {
				$personProfiles[$person['id_person']] = $personItem->getListData();
				$personProfiles[$person['id_person']]['role'] = $person['person_role'];
				$personProfiles[$person['id_person']]['roleName'] = $this->getPersonRoleName($person['person_role']);
				$this->persons[] = $personProfiles[$person['id_person']];
			}
		}
		$this->personsLoaded = true;
	}

	function getPersonRoleName($id_role) {
		switch ($id_role) {
			case self::ROLE_ABOUTA:
				return 'биограф';
				break;
			case self::ROLE_AUTHOR:
				return 'автор';
				break;
			case self::ROLE_DIRECT:
				return 'директор';
				break;
			case self::ROLE_EDITOR:
				return 'редактор';
				break;
			case self::ROLE_ILLUST:
				return 'иллюстратор';
				break;
			case self::ROLE_OFORMI:
				return 'оформитель';
				break;
			case self::ROLE_SOSTAV:
				return 'составитель';
				break;
			case self::ROLE_TRANSL:
				return 'переводчик';
				break;
		}
	}

	function getRightsholder() {
		if (!$this->rightsholderLoaded) {
			$this->loadRightsholder();
		}
		return $this->rightsholder;
	}

	function getTitle($asString = false) {
		if (!$this->loaded) {
			$this->load();
		}
		if ($asString) {
			return$this->data['subtitle'] ?
				$this->data['title'] . ' ' . $this->data['subtitle'] :
				$this->data['title'];
		}
		return array(
		    'title' => $this->data['title'],
		    'subtitle' => $this->data['subtitle']
		);
	}

	function getPersons() {
		if ($this->persons == null) {
			$this->loadPersons();
		}
		return $this->persons;
	}

	function getGenres() {
		if ($this->genres == null) {
			$this->loadGenres();
		}
		return $this->genres;
	}

	function getSeries() {
		if ($this->series == null) {
			$this->loadSeries();
		}
		return $this->series;
	}

	function getISBN() {
		if (!$this->loaded) {
			$this->load();
		}
		return $this->data['ISBN'];
	}

	function getIzdatel() {
		
	}

	function getAnnotation() {
		if (!$this->loaded) {
			$this->load();
		}
		return $this->data['description'];
	}

	function getRating() {
		
	}

	function getQuality() {
		if (!$this->loaded) {
			$this->load();
		}
		return isset($this->data['quality']) ? (int) $this->data['quality'] : 0;
	}

	function getTranslations() {
		
	}

	function isPublic() {
		if (!$this->loaded) {
			$this->load();
		}
		return $this->data['is_public'];
	}

	function getEditions() {
		
	}

	function getAuthors() {
		$out = array();
		$this->loadPersons();
		foreach ($this->persons as $person) {
			if ($person['role'] == self::ROLE_AUTHOR) {
				$out[] = $person;
			}
		}
		return $out;
	}

	function getAuthor($first_name = true, $last_name = true, $middle_name = true, $id = false) {
		$this->loadPersons();
		if (is_array($this->persons))
			foreach ($this->persons as $person) {
				if ($person['role'] == self::ROLE_AUTHOR && (!$id || $id == $person['id'])) { {
						$string = '';
						if ($first_name)
							$string.=' ' . $person['first_name'];
						if ($middle_name)
							$string.=' ' . $person['middle_name'];
						if ($last_name)
							$string.=' ' . $person['last_name'];
						return array($person['id'], trim($string));
					}
				}
			}
		return array('0', 'неизвестен');
	}

	function getAuthorId($id = false) {
		$this->loadPersons();
		if (is_array($this->persons))
			foreach ($this->persons as $person) {
				if ($person['role'] == self::ROLE_AUTHOR && (!$id || $id == $person['id'])) { {
						return $person['id'];
					}
				}
			}
		return 0;
	}

	/**
	 *
	 * @param string $mode small/big/normal
	 * @return string full url
	 */
	function getCover($mode = '') {
		$mode = $mode == 'normal' ? '' : $mode;
		if (!$this->loaded) {
			$this->load();
		}
		if ($this->data['is_cover']) {
			return Config::need('www_path') . '/static/upload/covers/' . (ceil($this->id / 5000)) . '/' . $this->id . $mode . '.jpg';
		}
		return Config::need('www_path') . '/static/upload/covers/default' . $mode . '.jpg';
	}

	function getReviews() {
		if (!$this->reviewsLoaded)
			$this->loadReviews();
		return $this->reviews;
	}

	function getFiles() {
		$ft = Config::need('filetypes');
		if (!$this->filesLoaded) {
			$this->loadFiles();
		}
		$out = array();
		foreach ($this->files as $filerow) {
			$out[$filerow['id']] = array(
			    'filetype' => $filerow['filetype'],
			    'filetypedesc' => $ft[$filerow['filetype']],
			    'is_default' => $filerow['is_default'],
			    'size' => $filerow['filesize'],
			    'modify_time' => $filerow['modify_time'],
			    'path' => getBookDownloadUrl($filerow['id'], $this->id, $filerow['filetype']),
			);
		}
		return $out;
	}

	function getMarkNumber() {
		$this->load();
		return round($this->data['mark']) / 10;
	}

	function getMarkRoundNumber() {
		$this->load();
		return round($this->data['mark'] / 10);
	}

	function getMarkPercents() {
		$this->load();
		return ($this->data['mark'] * 2);
	}

	function getListData($person_id = false) {
		list($aid, $aname) = $this->getAuthor(1, 1, 1, $person_id); // именно наш автор, если их там много
		$out = array(
		    'id' => $this->id,
		    'cover' => $this->getCover(),
		    'title' => $this->getTitle(true),
		    'author_id' => $aid,
		    'lastSave' => $this->data['modify_time'],
		    'path' => $this->getUrl(),
		    'mark' => $this->getMarkNumber(),
		    'mark_percents' => $this->getMarkPercents(),
		    'mark_number' => $this->getMarkRoundNumber(),
		);
		return $out;
	}

}