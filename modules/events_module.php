<?php

// модуль отвечает за отображение баннеров
class events_module extends BaseModule {
	const PER_PAGE = 20;

	function generateData() {
		global $current_user;
		$params = $this->params;

		$this->user_id = isset($params['user_id']) ? $params['user_id'] : $current_user->id;
		$this->post_id = isset($params['post_id']) ? $params['post_id'] : false;
		$this->type = isset($params['type']) ? $params['type'] : 'self';

		switch ($this->action) {
			case 'list':
				switch ($this->mode) {
					case 'last':
						$this->getEvents($all = true);
						break;
					default:
						$this->getEvents($all = false);
						break;
				}

				break;
			case 'new':
				break;
			case 'show':
				$this->getEvent();
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getEvent() {
		if (!$this->post_id || !$this->user_id)
			return;
		$query = 'SELECT `mongoid` FROM `events` WHERE `id`=' . (int) $this->post_id;
		$integer_id = Database::sql2single($query);
		if (!(int) $integer_id)
			return;
		$wall = MongoDatabase::getUserWallItem($integer_id, $this->user_id);
		$events = MongoDatabase::getWallEvents($wall);
		$this->_list($events, $item = true);
	}

	function _list($events, $item = false) {
		$count = isset($events['count']) ? $events['count'] : 0;
		unset($events['count']);
		$cond = new Conditions();
		$cond->setPaging($count, self::PER_PAGE);
		$this->data['conditions'] = $cond->getConditions();

		$book_ids = array();
		$user_ids = array();
		$mongoIds = array();
		$aids = array();
		$sids = array();
		$gids = array();
		foreach ($events as &$event) {
			$mongoIds[$event['id']] = Database::escape($event['id']);
			unset($event['likes']);
			$event['time'] = date('Y/m/d H:i', $event['wall_time']);
			if (isset($event['book_id'])) {
				$book_ids[$event['book_id']] = $event['book_id'];
			}

			if (isset($event['author_id'])) {
				$aids[$event['author_id']] = $event['author_id'];
			}

			if (isset($event['shelf_id'])) {
				$event['shelf_title'] = Config::$shelves[$event['shelf_id']];
			}



			if (isset($event['serie_id'])) {
				$sids[$event['serie_id']] = $event['serie_id'];
			}

			if (isset($event['genre_id'])) {
				$gids[$event['genre_id']] = $event['genre_id'];
			}

			if (isset($event['owner_id'])) {
				$user_ids[$event['owner_id']] = $event['owner_id'];
			}
			if ($event['user_id'])
				$user_ids[$event['user_id']] = $event['user_id'];
			if ($event['retweet_from']) {
				$user_ids[$event['retweet_from']] = $event['retweet_from'];
			}
			$comments = array();
			if (isset($event['comments'])) {
				$event['comments'] = array_slice($event['comments'], -15, 15, true);
				$i = 0;
				foreach ($event['comments'] as $id => $comment) {
					$i++;
					$user_ids[$comment['commenter_id']] = $comment['commenter_id'];
					$comments[$i] = array('parent_id' => $event['id'], 'id' => $id, 'commenter_id' => $comment['commenter_id'], 'comment' => $comment['comment'], 'time' => date('Y/m/d H:i', $comment['time']));
					if (isset($comment['answers'])) {
						$j = 0;
						foreach ($comment['answers'] as $ida => $answer) {
							$user_ids[$answer['commenter_id']] = $answer['commenter_id'];
							$comments[$i]['answers'][$j++] = array('parent_id' => $event['id'], 'id' => $ida, 'commenter_id' => $answer['commenter_id'], 'comment' => $answer['comment'], 'time' => date('Y/m/d H:i', $answer['time']));
						}
					}
				}
			}
			$event['comments'] = $comments;
			if (!$this->post_id && isset($event['review'])) { // short
				$event['body'] = $event['review'];
				unset($event['review']);
			}
			if (!$this->post_id && isset($event['short'])) { // short
				$event['body'] = $event['short'];
				unset($event['short']);
			}
		}

		if (count($mongoIds)) {
			$query = 'SELECT `id`,`mongoid` FROM `events` WHERE `mongoid` IN (' . implode(',', array_values($mongoIds)) . ')';
			$integer_ids = Database::sql2array($query, 'mongoid');
			if (count($integer_ids)) {
				foreach ($events as &$event) {
					if (isset($integer_ids[$event['id']]['id']))
						$event['link_url'] = 'user/' . $event['owner_id'] . '/wall/' . $integer_ids[$event['id']]['id'];
				}
			}
		}
		if (!$item) {
			$this->data['events'] = $events;

			$this->data['users'] = $this->getEventsUsers($user_ids);
			list($this->data['books'], $aids_) = $this->getEventsBooks($book_ids);
			$aids = array_merge($aids_, $aids);
			$this->data['authors'] = $this->getEventsAuthors($aids);
			$this->data['series'] = $this->getEventsSeries($sids);
			$this->data['genres'] = $this->getEventsGenres($gids);

			$this->data['events']['title'] = 'События';
			$this->data['events']['count'] = count($events);
			if ($this->type == 'not_self')
				$this->data['events']['self'] = $this->user_id;
		}else{ // one event
			$this->data['event'] = array_pop($events);

			$this->data['users'] = $this->getEventsUsers($user_ids);
			list($this->data['books'], $aids_) = $this->getEventsBooks($book_ids);
			$aids = array_merge($aids_, $aids);
			$this->data['authors'] = $this->getEventsAuthors($aids);
			$this->data['series'] = $this->getEventsSeries($sids);
			$this->data['genres'] = $this->getEventsGenres($gids);

			if ($this->type == 'not_self')
				$this->data['events']['self'] = $this->user_id;
		}
	}

	function getEventsSeries($sids) {
		if (!count($sids))
			return array();
		$query = 'SELECT id,name,title FROM `series` WHERE `id` IN(' . implode(',', $sids) . ')';
		$out = Database::sql2array($query);
		foreach ($out as &$r) {
			$r['path'] = Config::need('www_path') . '/s/' . $r['id'];
		}
		return $out;
	}

	function getEventsGenres($gids) {
		if (!count($gids))
			return array();
		$query = 'SELECT * FROM `genre` WHERE `id` IN(' . implode(',', $gids) . ')';
		$out = Database::sql2array($query);
		foreach ($out as &$r) {
			$r['path'] = Config::need('www_path') . '/g/' . $r['id'];
		}
		return $out;
	}

	function getEvents($all = false) {
		$cond = new Conditions();
		$cond->setPaging(100, self::PER_PAGE);
		$limit = $cond->getMongoLimit();
		if (!$all) {
			if (isset($this->params['select']) && $this->params['select'] == 'self') { // выбрали "только свои записи" на "моей стене"
				$wall = MongoDatabase::getUserWall((int) $this->user_id, $limit, self::PER_PAGE, 'self');
			}else
				$wall = MongoDatabase::getUserWall((int) $this->user_id, $limit, self::PER_PAGE, $this->type);
		}else {
			// показываем просто последнюю активность
			$events = MongoDatabase::getWallLastEvents(self::PER_PAGE);
			$this->_list($events);
			return;
		}
		$events = MongoDatabase::getWallEvents($wall);
		$this->_list($events);
	}

	function getEventsAuthors($aids) {
		if (!count($aids))
			return;
		$persons = Persons::getInstance()->getByIdsLoaded($aids);
		$out = array();
		foreach ($persons as $person) {
			$out[] = $person->getListData();
		}
		return $out;
	}

	function getEventsUsers($ids) {
		$users = Users::getByIdsLoaded($ids);
		$out = array();
		/* @var $user User */
		$i = 0;
		if (is_array($users))
			foreach ($users as $user) {
				$out[] = $user->getListData();
			}
		return $out;
	}

	function getEventsBooks($ids, $opts = array(), $limit = false) {
		$person_id = isset($opts['person_id']) ? $opts['person_id'] : false;
		$books = Books::getInstance()->getInstance()->getByIdsLoaded($ids);
		Books::getInstance()->getInstance()->LoadBookPersons($ids);
		$out = array();
		$aids = array();
		/* @var $book Book */
		$i = 0;
		if (is_array($books))
			foreach ($books as $book) {
				if ($limit && ++$i > $limit)
					return $out;
				$out[] = $book->getListData();
				list($author_id, $name) = $book->getAuthor();

				if ($author_id) {
					$aids[$author_id] = $author_id;
				}
			}
		return array($out, $aids);
	}

}