<?php

class JBaseModule{
	public $data;
	
	function __construct() {
		$this->process();
	}
	
	function process(){
		
	}
	
	function getJson(){
		return json_encode($this->data);
	}
}