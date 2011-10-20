<?php

class Conditions {

	public $paging;
	private $perPage;
	private $currentPage;
	private $totalCount;
	//
	public $sorting;
	//
	private $url;
	private $pagingParameterName = 'p';

	function __construct() {
		$this->url = Request::$get_normal;
	}

	private function getCurrentPage() {
		$p = isset(Request::$get_normal[$this->pagingParameterName]) ? (int) Request::$get_normal[$this->pagingParameterName] : 1;
		if ($p > $this->getLastPage())
			$p = $this->getLastPage();
		if ($p < 1)
			$p = 1;
		return $p;
	}

	private function getLastPage() {
		return ceil($this->totalCount / max(1,$this->perPage));
	}

	function getLimit() {
		return (($this->currentPage - 1) * $this->perPage) . ' , ' . $this->perPage;
	}
	
	function getMongoLimit() {
		return (($this->currentPage - 1) * $this->perPage);
	}

	function setSorting($options) {
		$other = ($this->getSortingOrder() == 'desc') ? 'asc' : 'desc';
		foreach ($options as $name => $option) {
			$opt = $option;
			if ($this->getSortingField() == $name) {
				$opt['current'] = 1;
				$opt['path'] = $this->preparePath(array(array('sort' => $name), array('order' => $other)));
			} else {
				$opt['path'] = $this->preparePath(array(array('sort' => $name), array('order' => 'asc')));
			}
			$this->sorting[$name] = $opt;
		}
	}

	function getSortingField() {
		$p = isset(Request::$get_normal['sort']) ? Request::$get_normal['sort'] : '';
		if(!isset($this->sorting[$p]))
			return '';
		return $p;
	}

	function getSortingOrderSQL() {
		$p = isset(Request::$get_normal['order']) ? Request::$get_normal['order'] : 'desc';
		$p = ($p == 'desc') ? 'DESC' : 'ASC';
		return $p;
	}

	function setPaging($count, $perPage, $pagingParameterName = 'p') {
		$this->pagingParameterName = $pagingParameterName;
		$this->totalCount = $count;
		$this->perPage = $perPage;
		$this->currentPage = $this->getCurrentPage();
		$this->addPage(1);
		for ($i = $this->currentPage - 10; $i < $this->currentPage + 10; $i++)
			$this->addPage($i);
		$this->addPage($this->getLastPage());
	}

	private function preparePath($arr) {
		$path = Request::$get_normal;
		foreach ($arr as $i) {
			foreach ($i as $f => $v) {
				$path[$f] = $v;
				$out = array();
				foreach ($path as $f => $v)
					$out[] = $f . '=' . $v;
			}
		}
		return Request::$url . '?' . implode('&', $out);
	}

	private function getSortingOrder() {
		$p = isset(Request::$get_normal['order']) ? Request::$get_normal['order'] : 'asc';
		$p = ($p == 'asc') ? 'asc' : 'desc';
		return $p;
	}

	private function addPage($id) {
		if (($id = (int) $id) < 1)
			return;
		if ($id > $this->getLastPage())
			return;
		$this->paging[$id] = array(
		    'title' => $id,
		    'path' => $this->preparePath(array(array($this->pagingParameterName => $id))),
		);
		if ($id == $this->getLastPage())
			$this->paging[$id]['last'] = 1;
		if ($id == 1)
			$this->paging[$id]['first'] = 1;

		if ($id == $this->getCurrentPage() + 1)
			$this->paging[$id]['next'] = 1;

		if ($id == $this->getCurrentPage() - 1)
			$this->paging[$id]['prev'] = 1;

		if ($id == $this->getCurrentPage())
			$this->paging[$id]['current'] = 1;
	}

	function getConditions() {
		$out = array();
		if (count($this->paging)>1)
			$out[] = array('mode' => 'paging', 'options' => $this->paging);
		if ($this->sorting)
			$out[] = array('mode' => 'sorting', 'options' => array_values($this->sorting));
		return $out;
	}

}