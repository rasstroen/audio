<?php

class features_module extends CommonModule {

	function setCollectionClass() {
		$this->Collection = Features::getInstance();
	}

	function _process($action, $mode) {
		switch ($action) {
			case 'list':
				switch ($mode) {
					default:
						$this->getFeaturesList();
						break;
				}
				break;
			case 'show':
				switch ($mode) {
					default:
						$this->_show($this->params['feature_id']);
						break;
				}
				break;
			case 'edit':
				switch ($mode) {
					default:
						$this->_show($this->params['feature_id']);
						break;
				}
				break;
			case 'new':
				switch ($mode) {
					default:
						$this->_new();
						break;
				}
				break;
			default:
				throw new Exception('no action #' . $this->action . ' for ' . $this->moduleName);
				break;
		}
	}

	function getFeaturesList() {
		$where = '';
		$data = $this->_list($where, false, 1);
		$this->data['feature_groups'] = array();
		$this->data['feature_groups'] = $this->getInGroup($data);

		$this->data['features']['title'] = 'Тесты';
		$this->data['features']['count'] = $this->getCountBySQL($where);
	}

	function getInGroup($data) {
		$groups = array();
		foreach ($data['features'] as $item) {
			$groups[$item['group_id']] = $item['group_id'];
		}
		$query = 'SELECT * FROM `feature_groups` WHERE `id` IN(' . implode(',', $groups) . ')';
		$groups = Database::sql2array($query, 'id');


		foreach ($data['features'] as $feature) {
			$groups[$feature['group_id']]['features'][] = $feature;
		}
		if (isset($groups[0]))
			$groups[0] = array('title' => 'без группы');

		return $groups;
	}

}