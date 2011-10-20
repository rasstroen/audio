<?php

class Persons extends Collection {

	public $className = 'Person';
	public $tableName = 'persons';
	public $itemName = 'author';
	public $itemsName = 'authors';

	public static function getInstance() {
		if (!self::$persons_instance) {
			self::$persons_instance = new Persons();
		}
		return self::$persons_instance;
	}

}