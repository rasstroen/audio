<?php

class BackendWriteModule extends BaseWriteModule {

	function process() {
		$params = Request::checkPostParameters(array('action' => 'string'));
		//die(print_r(Request::$post));
		switch ($params['action']) {
			case 'edit_pages':
				$this->editPages();

				break;
			case 'edit_pages_addmodule':
				$this->addPageModule();
				break;
			case 'edit_modules':
				$this->editModules();
				break;
		}
	}

	function addPageModule() {
		$id_page = Request::$post['id_page'];
		$id_modile = Request::$post['id_module'];
		Database::query('INSERT INTO `core_pages_modules` SET
				`id_page`=\'' . $id_page . '\',
				`id_module`=\'' . $id_modile . '\',
				`enabled`=1
					ON DUPLICATE KEY UPDATE		
				`enabled`=1
				');
	}

	function editPages() {
		if (isset(Request::$post['title'])) {
			foreach (Request::$post['title'][0] as $parent => $title) {
				if ($title) {
					$this->addPage($parent);
				}
			}
			// редактирование страниц
			foreach (Request::$post['id'] as $id) {
				$position = (int) Request::$post['position'][$id];
				$title = Request::$post['title'][$id];
				$name = Request::$post['name'][$id];
				$xslt = Request::$post['xslt'][$id];
				$uri_path = Request::$post['uri_path'][$id];
				$uri_redirect = Request::$post['uri_redirect'][$id];
				$xslt = Request::$post['xslt'][$id];
				$cache_sec = Request::$post['cache_sec'][$id];
				$parent = Request::$post['parent'][$id];
				$description = isset(Request::$post['description'][$id]) ? Request::$post['description'][$id] : '';
				if ($title)
					Database::query('INSERT INTO `core_pages` SET
				`id`=\'' . $id . '\',				
				`title`=\'' . $title . '\',
				`description`=\'' . $description . '\',
				`name`=\'' . $name . '\',
				`xslt`=\'' . $xslt . '\',
				`uri_path`=\'' . $uri_path . '\',
				`uri_redirect`=\'' . $uri_redirect . '\',
				`cache_sec`=\'' . $cache_sec . '\',
				`position` = ' . $position . ',
				`parent` = ' . $parent . '
					ON DUPLICATE KEY UPDATE
				`title`=\'' . $title . '\',
				`description`=\'' . $description . '\',
				`name`=\'' . $name . '\',
				`xslt`=\'' . $xslt . '\',
				`uri_path`=\'' . $uri_path . '\',
				`uri_redirect`=\'' . $uri_redirect . '\',
				`cache_sec`=\'' . $cache_sec . '\',
				`position` = ' . $position . ',
				`parent` = ' . $parent . '
				');
			}
			// модули
			$toupdate = array();
			$todelete = array();

			foreach (Request::$post['modules_pages'] as $id_page => $data) {
				foreach ($data as $id_module => $settings) {

					// неинхеритед 0 удаляем


					foreach (Request::$post['actions'][$id_page][$id_module] as $modk => $mod) {
						$roles = '';
						if (isset(Request::$post['pm'][$id_page][$id_module][$modk]))
							$block = (int) Request::$post['pm'][$id_page][$id_module][$modk];
						if (isset(Request::$post['pmr'][$id_page][$id_module][$modk]))
							$roles = implode(',', array_keys(Request::$post['pmr'][$id_page][$id_module][$modk]));
						
						$action = Request::$post['actions'][$id_page][$id_module][$modk];
						$mode = Request::$post['mode'][$id_page][$id_module][$modk];
						$params = $this->paramsTostring($id_page, $id_module, $action, $mode);
						$comment = Request::$post['comment'][$id_page][$id_module][$modk];
						$block = 0;
						$toupdate[] = '(' . $id_page . ',' . $id_module . ',' . (int) $settings['enabled'] . ',' . (int) $block . ',\'' . $roles . '\'' . ',\'' . $params . '\'' . ',\'' . $action . '\',\'' . $mode . '\',\'' . $comment . '\')';
						
						if (!isset($settings['inherited'][$modk])) {
							if ($settings['enabled'][$modk] == 0) {
								$todelete[] = '(' . $id_page . ',' . $id_module .',\''.$action.'\',\''.$mode.'\')';
							}
						}
					}

					// инхеритед с нулем апдейтим
				}
			}

			if (count($toupdate)) {
				$query = 'REPLACE INTO `core_pages_modules`(id_page,id_module,enabled,block,roles,params,action,mode,comment) VALUES ' . implode(',', $toupdate);
				Database::query($query);
			}
			if (count($todelete)) {
				$query = 'DELETE FROM `core_pages_modules` WHERE (id_page,id_module,action,mode) IN (' . implode(',', $todelete) . ')';
				Database::query($query);
			}
		}
	}

	function paramsTostring($id_page, $id_module, $action , $mode) {
		$string = array();
		
		foreach (Request::$post['paramname'][$id_page][$id_module] as $id => $name) {
			
			//paramtype
			//param
			
			if (!isset(Request::$post['paramname'][$id_page][$id_module][$id][$action]['m'.$mode]))
				continue;
			
			if (!(Request::$post['paramname'][$id_page][$id_module][$id][$action]['m'.$mode]))
				continue;
			

			$string[] = array(
			    'name' => Request::$post['paramname'][$id_page][$id_module][$id][$action]['m'.$mode],
			    'type' => Request::$post['paramtype'][$id_page][$id_module][$id][$action]['m'.$mode],
			    'value' => Request::$post['param'][$id_page][$id_module][$id][$action]['m'.$mode]);
		}

		return serialize($string);
	}

	function addPage($parent) {
		$position = (int) Request::$post['position'][0][$parent];
		$title = Request::$post['title'][0][$parent];
		$name = Request::$post['name'][0][$parent];
		$xslt = Request::$post['xslt'][0][$parent];
		$description = isset(Request::$post['description'][0][$parent]) ? Request::$post['description'][0][$parent] : '';
		Database::query('INSERT INTO `core_pages` SET
				`title`=\'' . $title . '\',
				`description`=\'' . $description . '\',
				`name`=\'' . $name . '\',
				`xslt`=\'' . $xslt . '\',
				`position` = ' . $position . ',
				`parent` = ' . $parent);
	}

	function editModule($id) {
		$id = max(0, (int) $id);
		if (!$id)
			return;
		$title = Request::$post['title'][$id];
		$name = Request::$post['name'][$id];
		$xslt = Request::$post['xslt'][$id];
		$xHTML = isset(Request::$post['xHTML'][$id]) ? 1 : 0;
		$cache_sec = max(0, (int) Request::$post['cache_sec'][$id]);
		$inherited = max(0, (int) (Request::$post['inherited'][$id] == 'on'));
		$delete = Request::$post['delete'][$id] === 'on';
		if ($delete) {
			Database::query('DELETE FROM `core_modules` WHERE `id`=' . $id);
			return;
		}

		if ($title && $name && $xslt) {
			Database::query('INSERT INTO `core_modules` SET
				`id` = ' . $id . ',
				`title`=\'' . $title . '\',
				`name`=\'' . $name . '\',
				`xslt`=\'' . $xslt . '\',
				`cache_sec`=' . $cache_sec . ',
				`xHTML` = ' . $xHTML . ',
				`inherited` = ' . $inherited . '
				ON DUPLICATE KEY UPDATE
				`id` = ' . $id . ',
				`title`=\'' . $title . '\',
				`name`=\'' . $name . '\',
				`xslt`=\'' . $xslt . '\',
				`cache_sec`=' . $cache_sec . ',
				`xHTML` = ' . $xHTML . ',
				`inherited` = ' . $inherited . '
				');
		}
	}

	function addModule() {
		$title = Request::$post['title'][0];
		$name = Request::$post['name'][0];
		$xslt = Request::$post['xslt'][0];
		$inherited = (int) (Request::$post['inherited'][$id] == 'on');

		if ($title && $name && $xslt) {
			Database::query('INSERT INTO `core_modules` SET
				`title`=\'' . $title . '\',
				`name`=\'' . $name . '\',
				`inherited` = ' . $inherited . ',
				`xslt`=\'' . $xslt . '\'');
		}
	}

	function editModules() {
		if (isset(Request::$post['id'][0])) {
			$this->addModule();
		}
		foreach (Request::$post['id'] as $id) {
			$this->editModule($id);
		}
	}

}