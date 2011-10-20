<?php

// модуль отвечает за отображение баннеров
class authors_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Persons::getInstance();
	}

	function _process() {
		switch ($this->action) {
			case 'show':
				switch ($this->mode) {
					default:
						$this->_show($this->params['author_id']);
						break;
				}
				break;
			case 'edit':
				switch ($this->mode) {
					default:
						$this->_show($this->params['author_id']);
						$this->_edit();
						$this->getAuthorRelations();
						break;
				}
				break;
			case 'new':
				switch ($this->mode) {
					default:
						$this->_edit();
						break;
				}
				break;
			case 'list':
				switch ($this->mode) {
					case 'editions':
						$this->getEditions();
						break;
					case 'translations':
						$this->getTranslations();
						break;
					case 'loved':
						$this->getLoved();
						break;
					case 'new':
						$this->getNew();
						break;
					case 'search':
						$this->getSearch();
						break;
					default:
						throw new Exception('no mode #' . $this->mode . ' for ' . $this->moduleName);
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getTranslations() {
		if (!$this->params['author_id'])
			return;
		$ids = array();
		$person = Persons::getInstance()->getByIdLoaded($this->params['author_id']);
		$rels = PersonRelations::getPersonRelations($this->params['author_id']);
		/* @var $person Person */
		if ($rels) {
			$ids = Database::sql2array('SELECT `id` FROM `persons` WHERE `id` IN(' . implode(',', array_keys($rels)) . ') AND `is_deleted`=0 AND `id`<>' . $this->params['author_id'] . ' AND `author_lang`<>' . $person->getLangId(), 'id');
		}else
			$ids = array();

		$this->data = $this->_idsToData(array_keys($ids));
		$this->data['authors']['title'] = 'Переводы';
		$this->data['authors']['count'] = count($ids);
	}

	function getEditions() {
		if (!$this->params['author_id'])
			return;
		$ids = array();
		$person = Persons::getInstance()->getByIdLoaded($this->params['author_id']);
		$rels = PersonRelations::getPersonRelations($this->params['author_id']);
		/* @var $book Book */
		if ($rels) {
			$ids = Database::sql2array('SELECT `id` FROM `persons` WHERE `id` IN(' . implode(',', array_keys($rels)) . ') AND `is_deleted`=0 AND `id`<>' . $this->params['author_id'] . ' AND `author_lang`=' . $person->getLangId(), 'id');
		}else
			$ids = array();

		$this->data = $this->_idsToData(array_keys($ids));
		$this->data['authors']['title'] = 'Редакции';
		$this->data['authors']['count'] = count($ids);
	}

	function getSearch() {
		$query_string = isset(Request::$get_normal['q']) ? Request::$get_normal['q'] : false;
		$query_string_prepared = ('%' . mysql_escape_string($query_string) . '%');
		$where = '`first_name` LIKE \'' . $query_string_prepared . '\'';
		$where .= 'OR `last_name` LIKE \'' . $query_string_prepared . '\'';
		$where .= 'OR `middle_name` LIKE \'' . $query_string_prepared . '\'';
		$this->_list($where);

		$this->data['authors']['title'] = 'Авторы по запросу «' . $query_string . '»';
		$this->data['authors']['count'] = $this->getCountBySQL($where);
	}

	function getAuthorRelations() {
		if (!$this->params['author_id'])
			return;
		$person = Persons::getInstance()->getByIdLoaded($this->params['author_id']);
		foreach (PersonRelations::$relation_types as $id => $title) {
			$this->data['author']['relation_types'][] = array('id' => $id, 'name' => $title);
		}
		/* @var $person Person */
		if (!$person->loaded) {
			return false;
		}

		$aids = array();
		if ($basket_id = $person->getBasketId()) {
			$query = 'SELECT * FROM `person_basket` WHERE `id_basket`=' . $basket_id;
			$relations = Database::sql2array($query);
			foreach ($relations as $relation) {
				$aids[$relation['id_person']] = $relation['id_person'];
			}
			Persons::getInstance()->getByIdsLoaded($aids);
			foreach ($relations as &$relation) {
				if ($relation['id_person'] == $person->id)
					continue;
				$relperson = Persons::getInstance()->getByIdLoaded($relation['id_person']);
				$aids[$relation['id_person']] = $relation['id_person'];

				if ($person->getLangId() != $relperson->getLangId())
					$relation['type'] = PersonRelations::RELATION_TYPE_TRANSLATE;
				else
					$relation['type'] = PersonRelations::RELATION_TYPE_EDITION;
				$relation['relation_type_name'] = PersonRelations::$relation_types[$relation['type']];
				$relation['id1'] = $person->id;
				$relation['id2'] = $relation['id_person'];
				$this->data['author']['relations'][] = $relation;
			}
		}
		$query = 'SELECT `id`,`is_p_duplicate` FROM `persons` WHERE `is_p_duplicate`=' . $person->id . ' OR `id`=' . $person->id;
		$rows = Database::sql2array($query);
		if (count($rows)) {
			foreach ($rows as $row) {
				if ($row['is_p_duplicate']) {
					$relation = array(
					    'desc' => $row['id'] . ' is duplicate for ' . $row['is_p_duplicate'],
					    'id2' => (int) $row['id'],
					    'id1' => (int) $row['is_p_duplicate'],
					    'id_person' => (int) $row['id'],
					    'type' => PersonRelations::RELATION_TYPE_DUPLICATE,
					    'relation_type_name' => PersonRelations::$relation_types[PersonRelations::RELATION_TYPE_DUPLICATE]
					);
					$this->data['author']['relations'][] = $relation;
					$aids[$row['id']] = $row['id'];
					$aids[$row['is_p_duplicate']] = $row['is_p_duplicate'];
				}
			}
		}

		$data = $this->_idsToData(array_keys($aids));
		$this->data['author']['relations']['authors'] = $data['authors'];
	}
	

	function _edit() {
		foreach (Config::$langRus as $code => $title) {
			$this->data['author']['lang_codes'][] = array(
			    'id' => Config::$langs[$code],
			    'code' => $code,
			    'title' => $title,
			);
		}
	}

	function getNew() {
		$ago = -1;

		$where = '`a_add_time`>' . $ago . ' AND `is_deleted`=0';
		$sortings = array(
		    'a_add_time' => array('title' => 'по дате добавления'),
		    'rating' => array('title' => 'по популярности'),
		);
		$this->_list($where, $sortings);

		$this->data['authors']['title'] = 'Новые авторы';
		$this->data['authors']['count'] = $this->getCountBySQL($where);
		$this->data['authors']['link_title'] = 'Все новые авторы';
		$this->data['authors']['link_url'] = '/';
	}

	function getLoved() {
		if (!$this->params['user_id'])
			return;
		$user = Users::getById($this->params['user_id']);
		/* @var $user User */
		$ids = $user->getLoved(Config::$loved_types['author']);

		$this->data = $this->_idsToData(array_keys($ids));
		$this->data['authors']['title'] = 'Любимые авторы';
		$this->data['authors']['count'] = count($ids);
		$this->data['authors']['link_title'] = 'Все любимые авторы';
		$this->data['authors']['link_url'] = '/';
	}

}