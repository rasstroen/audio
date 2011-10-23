<?php

class Features extends Collection {

	public $className = 'Feature';
	public $tableName = 'features';
	public $itemName = 'feature';
	public $itemsName = 'features';

	public static function getInstance() {
		if (!self::$features_instance) {
			self::$features_instance = new Features();
		}
		return self::$features_instance;
	}

	public function _create($data) {
		$item = new $this->className(0);
		$createdId = $item->_create($data);
		header('Location:' . Config::need('www_path') . '/features/' . $createdId);
		exit();
	}

}