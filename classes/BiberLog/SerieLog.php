<?php

class SerieLog extends BiberLog {

	private static $fields = array(
	    'title' => 1,
	    'description' => 2,
	    'id_parent' => 3,
	);

	public static function addLog($changedData, $oldData, $id_serie = false) {
		if(!$id_serie)
			throw new Exception('id_serie missed for log save');
		foreach ($changedData as $field => $newValue) {
			if (!isset(self::$fields[$field])) {
				throw new Exception('no field #' . $field . ' for Log');
			}
			if ($oldData[$field] != $newValue) {
				self::setChangedField($field, $oldData[$field], $newValue);
			}
		}
		if(count($changedData)) {
			self::setIdField('id_serie', $id_serie);
		}
	}

}