<?php

// модуль отвечает за отображение баннеров
class series_module extends BaseModule {
	const PER_PAGE = 30;

	function generateData() {
		global $current_user;
		$params = $this->params;
		$this->series_id = isset($params['series_id']) ? $params['series_id'] : 0;
		$this->user_id = isset($params['user_id']) ? $params['user_id'] : $current_user->id;
		if (!is_numeric($this->user_id)) {
			$query = 'SELECT `id` FROM `users` WHERE `nickname`=' . Database::escape($this->user_id);
			$this->user_id = (int) Database::sql2single($query);
		}
		switch ($this->action) {
			case 'show':
				$this->getOne();
				break;
			case 'edit':
				$this->getOne();
				break;
			case 'new':

				break;
			case 'list':
				switch ($this->mode) {
					case 'loved':
						$this->getLoved();
						break;
					default:
						$this->getAll();
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getLoved() {
		if (!$this->user_id)
			return;
		$user = new User($this->user_id);
		$ids = $user->getLoved(Config::$loved_types['serie']);

		if (count($ids)) {
			$query = 'SELECT * FROM `series` WHERE `id` IN(' . implode(',', $ids) . ')';
			$series = Database::sql2array($query);
			foreach ($series as &$serie) {
				$serie['path'] = Config::need('www_path') . '/series/' . $serie['id'];
			}
			$this->data['series'] = $series;
		}
		$this->data['series']['title'] = 'Любимые серии';
		$this->data['series']['count'] = count($ids);
		$this->data['series']['link_title'] = 'Все любимые серии';
		$this->data['series']['link_url'] = 'user/' . $this->user_id . '/series/loved';
	}

	function getAll() {
		$count = Database::sql2single('SELECT COUNT(1) FROM `series` WHERE `books_count`>0 AND `id_parent`=0 AND `is_deleted`=0');
		$cond = new Conditions();

		$cond->setPaging($count, self::PER_PAGE);
		$limit = $cond->getLimit();
		$this->data['conditions'] = $cond->getConditions();

		$series = Database::sql2array('SELECT id,title,position,books_count FROM `series` WHERE `books_count`>0 AND `id_parent`=0 AND `is_deleted`=0 ORDER BY `books_count` DESC LIMIT ' . $limit, 'id');

		$series_books = Database::sql2array('SELECT * FROM `book_series` WHERE id_series IN (' . implode(',', array_keys($series)) . ') ');
		$bid = array();

		$cnt = array();
		$series_books_p = array();
		foreach ($series_books as $sb) {
			$cnt[$sb['id_series']] = isset($cnt[$sb['id_series']]) ? $cnt[$sb['id_series']] + 1 : 1;
			if ($cnt[$sb['id_series']] > 10)
				continue;
			$series_books_p[$sb['id_series']][] = $sb;
			$bid[$sb['id_book']] = $sb['id_book'];
		}
		if (count($bid)) {
			Books::getInstance()->getByIdsLoaded($bid);
			Books::getInstance()->LoadBookPersons($bid);
		}
		$aids = array();
		foreach ($series_books_p as &$sb) {
			foreach ($sb as &$bookrow) {
				$book = Books::getInstance()->getById($bookrow['id_book']);
				/* @var $book Book */
				$bookrow = $book->getListData();
				list($aid, $data) = $book->getAuthor();
				$aids[$aid] = $aid;
			}
		}


		foreach ($series as $id => &$ser) {
			$this->data['series'][$id] = $ser;
			$this->data['series'][$id]['path'] = Config::need('www_path') . '/series/' . $ser['id'];
			$this->data['series'][$id]['books'] = isset($series_books_p[$id]) ? $series_books_p[$id] : array();
			$this->data['series'][$id]['books']['count'] = $ser['books_count'];
			$this->data['series'][$id]['books']['title'] = $ser['title'];
			$this->data['series'][$id]['books']['link_url'] = 's/' . $ser['id'];
			$this->data['series'][$id]['books']['link_title'] = 'Смотреть серию';
			unset($this->data['series'][$id]['books_count']);
		}
		if (count($aids)) {
			$persons = Persons::getInstance()->getByIdsLoaded($aids);
			foreach ($persons as $person) {
				$this->data['authors'][] = $person->getListData();
			}
		}

		$this->data['series']['count'] = $count;
	}

	function getOne() {
		if (!$this->series_id)
			return;
		$series = Database::sql2array('SELECT id,title,position,books_count,id_parent,description FROM `series` WHERE (`id`=' . $this->series_id . ' OR `id_parent`=' . $this->series_id . ') AND `is_deleted`=0', 'id');
		$parent_id = $series[$this->series_id]['id_parent'];
		if ($parent_id) {
			$parentInfo = Database::sql2row('SELECT id,title,position,books_count,id_parent FROM `series` WHERE `id`=' . $parent_id, 'id');
		}else
			$parentInfo = array();


		$cond = new Conditions();
		$cnt= Database::sql2single('SELECT COUNT(1) FROM `book_series` WHERE id_series =' . $this->series_id );
		$cond->setPaging($cnt, self::PER_PAGE);
		$limit = $cond->getLimit();
		$this->data['conditions'] = $cond->getConditions();
		$series_books = Database::sql2array('SELECT * FROM `book_series` WHERE id_series =' . $this->series_id . ' LIMIT ' . $limit);


		$bid = array();
		$cnt = array();
		$series_books_p = array();
		foreach ($series_books as $sb) {
			$cnt[$sb['id_series']] = isset($cnt[$sb['id_series']]) ? $cnt[$sb['id_series']] + 1 : 1;
			$series_books_p[$sb['id_series']][] = $sb;
			$bid[$sb['id_book']] = $sb['id_book'];
		}
		$aids = array();
		if (count($bid)) {
			Books::getInstance()->getByIdsLoaded($bid);
			Books::getInstance()->LoadBookPersons($bid);
		}

		foreach ($series_books_p as &$sb) {
			foreach ($sb as &$bookrow) {
				$book = Books::getInstance()->getById($bookrow['id_book']);
				list($aid, $aname) = $book->getAuthor(1, 1, 1); // именно наш автор, если их там много
				$bookrow = $book->getListData();
				$aids[$aid] = $aid;
			}
		}
		if (count($aids)) {
			$persons = Persons::getInstance()->getByIdsLoaded($aids);
			foreach ($persons as $person) {
				$this->data['authors'][] = $person->getListData();
			}
		}

		$this->data['serie']['series'] = array();
		$series[$this->series_id]['path'] = Config::need('www_path') . '/s/' . $this->series_id;
		$this->data['serie'] = $series[$this->series_id];
		Request::pass('serie-title', $this->data['serie']['title']);
		$this->data['serie']['books'] = isset($series_books_p[$this->series_id]) ? $series_books_p[$this->series_id] : array();
		$this->data['serie']['books']['count'] = isset($cnt[$this->series_id]) ? $cnt[$this->series_id] : 0;

		foreach ($series as $id => $ser) {
			if ($ser['id'] == $this->series_id) {
				unset($this->data['serie']['books_count']);
				continue;
			} else {
				$ser['path'] = Config::need('www_path') . '/s/' . $ser['id'];
				$this->data['serie']['series'][$id] = $ser;
				$this->data['serie']['series'][$id]['books'] = isset($series_books_p[$id]) ? $series_books_p[$id] : array();
				$this->data['serie']['series'][$id]['books']['count'] = $ser['books_count'];
				unset($this->data['serie']['series'][$id]['books_count']);
			}
		}
		if ($parentInfo) {
			$parentInfo['path'] = Config::need('www_path') . '/s/' . $parentInfo['id'];
			$this->data['serie']['parent'][] = $parentInfo;
		}
	}

	//old
	function getOneFull() {
		if (!$this->series_id)
			return;
		$series = Database::sql2array('SELECT id,title,position,books_count,id_parent,description FROM `series` WHERE `id`=' . $this->series_id . ' OR `id_parent`=' . $this->series_id, 'id');
		$parent_id = $series[$this->series_id]['id_parent'];
		if ($parent_id) {
			$parentInfo = Database::sql2array('SELECT id,title,position,books_count,id_parent FROM `series` WHERE `id`=' . $parent_id, 'id');
		}else
			$parentInfo = array();
		$series_books = Database::sql2array('SELECT * FROM `book_series` WHERE id_series IN (' . implode(',', array_keys($series)) . ') AND `is_deleted`=0');
		$bid = array();

		$cnt = array();
		$series_books_p = array();
		foreach ($series_books as $sb) {
			$cnt[$sb['id_series']] = isset($cnt[$sb['id_series']]) ? $cnt[$sb['id_series']] + 1 : 1;

			$series_books_p[$sb['id_series']][] = $sb;
			$bid[$sb['id_book']] = $sb['id_book'];
		}
		$aids = array();
		if (count($bid)) {
			Books::getInstance()->getByIdsLoaded($bid);
			Books::getInstance()->LoadBookPersons($bid);
		}

		foreach ($series_books_p as &$sb) {
			foreach ($sb as &$bookrow) {
				$book = Books::getInstance()->getById($bookrow['id_book']);
				list($aid, $aname) = $book->getAuthor(1, 1, 1); // именно наш автор, если их там много
				$bookrow = $book->getListData();
				$aids[$aid] = $aid;
			}
		}
		if (count($aids)) {
			$persons = Persons::getInstance()->getByIdsLoaded($aids);
			foreach ($persons as $person) {
				$this->data['authors'][] = $person->getListData();
			}
		}

		$this->data['serie']['series'] = array();

		$this->data['serie'] = $series[$this->series_id];
		$this->data['serie']['books'] = isset($series_books_p[$this->series_id]) ? $series_books_p[$this->series_id] : array();
		$this->data['serie']['books']['count'] = isset($cnt[$this->series_id]) ? $cnt[$this->series_id] : 0;

		foreach ($series as $id => $ser) {
			if ($ser['id'] == $this->series_id) {
				unset($this->data['serie']['books_count']);
				continue;
			} else {
				$ser['path'] = Config::need('www_path') . '/series/' . $ser['id'];
				$this->data['serie']['series'][$id] = $ser;
				$this->data['serie']['series'][$id]['books'] = isset($series_books_p[$id]) ? $series_books_p[$id] : array();
				$this->data['serie']['series'][$id]['books']['count'] = $ser['books_count'];
				unset($this->data['serie']['series'][$id]['books_count']);
			}
		}
		$this->data['serie']['parent'] = $parentInfo;
	}

}