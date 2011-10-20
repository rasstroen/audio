<?php

// модуль отвечает за отображение баннеров
class reviews_module extends BaseModule {

	function generateData() {
		global $current_user;
		$params = $this->params;
		$this->target_id = isset($params['target_id']) ? $params['target_id'] : 0;
		$this->target_type = isset($params['target_type']) ? $params['target_type'] : 0;
		$this->target_user = isset($params['target_user']) ? $params['target_user'] : 0;



		switch ($this->action) {
			case 'list':
				switch ($this->mode) {
					case 'rates':
						$this->getRates();
						break;
					default:
						$this->getReviews();
						break;
				}
				break;
			case 'new':
				switch ($this->mode) {

					default:
						$this->getUserReview();
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getUserReview() {
		global $current_user;
		if (!$current_user->authorized)
			return;
		$res = MongoDatabase::findReviewEvent($current_user->id, $this->target_id);
		$this->data = $this->_item($res);
		$this->data['review']['target_id'] = $this->target_id;
		$this->data['review']['target_type'] = $this->target_type;
		$this->data['review']['rate'] = isset($this->data['review']['rate']) ?
			$this->data['review']['rate'] :
			Database::sql2single('SELECT `rate` FROM `book_rate` WHERE `id_book` =' . $this->target_id . ' AND `id_user`=' . $current_user->id);
	}

	function _item($data) {
		$out = array();
		$usrs = array();
		if (is_array($data)) {
			foreach ($data as $row) {
				$out['review'] = array(
				    'id_user' => $row['id_user'],
				    'time' => date('Y-m-d H:i', $row['time']),
				    'rate' => $row['rate'],
				    'html' => $row['comment'],
				    'likesCount' => (int) $row['likesCount'],
				);
				$usrs[$row['id_user']] = $row['id_user'];
			}
		}
		if (count($usrs)) {
			$users = Users::getByIdsLoaded($usrs);
			foreach ($users as $user) {
				$out['users'][] = $user->getListData();
			}
		}
		return $out;
	}

	function _list($data, $uids = false) {
		$out = array();
		$usrs = array();
		if ($uids) {
			foreach ($uids as $id) {
				$usrs[$id] = $id;
			}
		}
		$mongoIds = array();
		if (is_array($data)) {
			foreach ($data as $row) {
				$out['reviews'][] = array(
				    'id' => $row['_id']->{'$id'},
				    'id_user' => $row['user_id'],
				    'time' => date('Y-m-d H:i', $row['time']),
				    'rate' => isset($row['mark']) ? $row['mark'] : 0,
				    'html' => isset($row['review']) ? $row['review'] : false,
				    'book_id' => $row['book_id'],
				    'likesCount' => (int) isset($row['likesCount']) ? $row['likesCount'] : 0,
				);
				$usrs[$row['user_id']] = $row['user_id'];
				$mongoIds[] = $row['_id']->{'$id'};
			}
		}
		if (count($usrs)) {
			$users = Users::getByIdsLoaded($usrs);
			foreach ($users as $user) {
				$out['users'][$user->id] = $user->getListData();
			}
		}
		foreach ($usrs as $id => $idd) {
			if (!isset($out['users'][$id])) {
				$out['users'][$user->id] = array(
				    'id' => $id,
				);
			}
		}

		if ($this->target_id) {
			if (count($mongoIds)) {
				$query = 'SELECT `id`,`mongoid` FROM `events` WHERE `mongoid` IN (\'' . implode('\',\'', array_values($mongoIds)) . '\')';
				$integer_ids = Database::sql2array($query, 'mongoid');
				if (count($integer_ids)) {
					foreach ($out['reviews'] as &$review) {
						if (isset($integer_ids[$review['id']]))
							$review['path'] = Config::need('www_path') . '/user/' . $review['id_user'] . '/wall/' . $integer_ids[$review['id']]['id'];
					}
				}
			}
		}
		return $out;
	}

	function getRates() {
		//marks
		$res = MongoDatabase::findReviewMarkEvents($this->target_id);
		$this->data = $this->_list($res, $uids = array());

		$this->data['reviews']['target_id'] = $this->target_id;
		$this->data['reviews']['target_type'] = $this->target_type;
	}

	function getReviews() {
		if ($this->target_user) {
			/* $query = 'SELECT * FROM `reviews` WHERE `id_user`=' . $this->target_user . ' ORDER BY `time` DESC';
			  $res = Database::sql2array($query);
			  $mongoIds = array();
			  $this->data = $this->_list($res, $mongoIds);
			  $bids = array();
			  foreach ($this->data['reviews'] as $row) {
			  $bids[$row['book_id']] = $row['book_id'];
			  }
			  $books = Books::getInstance()->getByIdsLoaded(array_keys($bids));
			  Books::getInstance()->LoadBookPersons(array_keys($bids));

			  foreach ($books as $book) {
			  $this->data['books'][] = $book->getListData();
			  }
			  $this->data['reviews']['target_id'] = $this->target_id;
			  $this->data['reviews']['target_type'] = $this->target_type; */
		} else {
			// reviews
			$res = MongoDatabase::findReviewEvents($this->target_id);
			$this->data = $this->_list($res, $uids = array());

			$this->data['reviews']['target_id'] = $this->target_id;
			$this->data['reviews']['target_type'] = $this->target_type;
		}
	}

}
