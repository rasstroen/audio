<?php

class BaseObjectClass {

	public $exists = false;
	public $fieldsMap = array();
	public $id;

	function _show() {
		throw new Exception('BaseObjectClass::_show must be implemeted');
	}

	function _create($data, $tableName) {
		$q = array();
		$this->dropCache();
		foreach ($data as $field => $value) {
			if (isset($this->fieldsMap[$field])) {
				$q[] = '`' . $field . '`=' . Database::escape($value);
			}else
				throw new Exception('_create failed: illegal field #' . $field);
		}
		if (count($q)) {
			Database::query('INSERT INTO `' . $tableName . '` SET ' . implode(',', $q));
			return $lid = Database::lastInsertId();
		}
	}

	function _update($data, $tableName) {
		$q = array();
		$this->dropCache();
		foreach ($data as $field => $value) {
			if (isset($this->fieldsMap[$field])) {
				$q[] = '`' . $field . '`=' . Database::escape($value);
			}else
				throw new Exception('_create failed: illegal field #' . $field);
		}
		if (count($q)) {
			Database::query('UPDATE `' . $tableName . '` SET ' . implode(',', $q) . ' WHERE `id`=' . $this->id);
			return $lid = Database::lastInsertId();
		}
	}

}