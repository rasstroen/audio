<?php

class BaseObjectClass {

	public $exists = false;

	function _show() {
		throw new Exception('BaseObjectClass::_show must be implemeted');
	}

}