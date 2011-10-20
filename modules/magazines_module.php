<?php

// модуль отвечает за отображение баннеров
class magazines_module extends BaseModule {

	function generateData() {
		global $current_user;
		$params = $this->params;
		$this->magazine_id = isset($params['magazine_id']) ? $params['magazine_id'] : 0;

		switch ($this->action) {
			case 'show':
				$this->getOne();
				break;
			case 'edit':
				$this->getOne();
				$this->getEditingInfo();
				break;
			case 'new':

				break;
			case 'list':
				switch ($this->mode) {
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

	function getEditingInfo() {
		foreach (Config::$langRus as $code => $title) {
			$this->data['magazine']['lang_codes'][] = array(
			    'id' => Config::$langs[$code],
			    'code' => $code,
			    'title' => $title,
			);
		}
	}

	function getAll() {
		// вся периодика
		$query = 'SELECT `id`,`title`,`first_year`,`last_year` FROM `magazines` ORDER BY `last_year` DESC';
		$magazines = Database::sql2array($query);
		foreach ($magazines as &$m) {
			$m['path'] = Magazine::_getUrl($m['id']);
		}
		$this->data['magazines'] = $magazines;
	}

	function getOne() {
		$m = new Magazine($this->magazine_id);
		$m->load();
		$this->data['magazine']['id'] = $m->id;
		$langId = $m->data['id_lang'];
		$langCode = Config::need('default_language');
		foreach (Config::$langs as $code => $id_lang) {
			if ($id_lang == $langId) {
				$langCode = $code;
			}
		}
		$this->data['magazine']['lang_code'] = $langCode;
		$this->data['magazine']['lang_title'] = Config::$langRus[$langCode];
		$this->data['magazine']['lang_id'] = $langId;
		$this->data['magazine']['path'] = $m->getUrl();

		$this->data['magazine']['isbn'] = $m->data['ISBN'];
		$this->data['magazine']['cover'] = $m->getCover();

		$this->data['magazine']['years'] = $m->getPeriodMap();
		$this->data['magazine']['title'] = $m->data['title'];
		$this->data['magazine']['rightholder'] = $m->data['rightsholder'] ? $m->data['rightsholder'] : '';
		$this->data['magazine']['annotation'] = $m->data['annotation'];
	}

}
