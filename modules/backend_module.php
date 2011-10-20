<?php

class backend_module extends BaseModule {

	/**
	 *  @property CurrentUser $user
	 */
	public $user;

	/**
	 * чтобы закешировать ноду юзера, нужно чтобы этот юзер не был авторизован
	 * авторизованных юзеров хранить в кеше не будем
	 */
	function generateData() {
		global $current_user;
		/* @var $current_user CurrentUser */
		if ($current_user->getRole() !== User::ROLE_SITE_ADMIN) {
			//throw new Exception('Access');
		}
		if (Request::get(1) == 'generate') {
			$this->generateLibrary();
		}
		$this->data['data'] = array();
		$this->data['data']['page'] = Request::get(0, 'pages');
		switch ($this->data['data']['page']) {
			case 'pages':
				$this->moduleList();
				$this->getPages();
				$this->data['roles'] = $this->getRolesFromString(false);
				break;
			case 'modules':
				$this->getModules();
				break;
		}
		$this->data['blocks'] = array();
	}

	function moduleList() {
		$query = 'SELECT * FROM `core_modules`';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			$this->data['moduleList'][] = $pagerow;
		}
	}

	function getPages() {
		$query = 'SELECT * FROM `core_pages`';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			$pagerow['modules'] = $this->getModules($pagerow['id']);
			$this->data['pages'][] = $pagerow;
		}
		$this->data['inherited_modules'] = $this->getInheritedModules(0);
	}

	function getInheritedModules() {
		$out = array();

		$query = 'SELECT * FROM `core_modules` WHERE inherited=1';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			$out[] = $pagerow;
		}
		return $out;
	}

	function getRolesFromString($s) {
		$out = array();
		if (!$s) {
			foreach (Users::$rolenames as $id => $name)
				$out[$id] = array('id' => $id, 'name' => $name);
			return $out;
		}
		$ids = explode(',', $s);
		foreach ($ids as $id) {
			$out[$id] = array('id' => $id, 'name' => Users::$rolenames[$id]);
		}
		return $out;
	}

	function getParamsFromString($a) {
		$out = array();
		if (!$a)
			return false;
		$r = unserialize($a);
		foreach($r as $x){
			if($x['name']) $out[]=$x;
		}
		return $out;
	}

	function getModules($id_page = false) {
		$out = array();
		if ($id_page === false)
			$query = 'SELECT * FROM `core_modules`';
		else
			$query = 'SELECT * FROM `core_modules` CM
				JOIN `core_pages_modules` CPM 
				ON (CPM.id_module = CM.id AND CPM.id_page=' . $id_page . ')';
		$res = Database::sql2array($query);

		foreach ($res as $pagerow) {
			if ($id_page === false) {
				$this->data['modules'][] = $pagerow;
			} else {
				$pagerow['roles'] = $this->getRolesFromString($pagerow['roles']);
				$pagerow['params'] = $this->getParamsFromString($pagerow['params']);
				$out[] = $pagerow;
			}
		}
		return $out;
	}

	function genRoles($ids) {
		$out = array();
		foreach ($ids as $id) {
			$out[$id] = $id;
		}
		return $out;
	}

	function generateLibrary() {
		$phplib_pages = Config::need('phplib_pages_path');
		$phplib_modules = Config::need('phplib_modules_path');
		$phplib_map = Config::need('phplib_modules_path');
		// генерируем модули
		$phplib_pages.='/LibPages.php';
		$phplib_modules.='/LibModules.php';
		$phplib_map.='/LibMap.php';

		$pages = Database::sql2array('SELECT * FROM `core_pages`', 'id');
		$modules = Database::sql2array('SELECT * FROM `core_modules`', 'id');
		$pages_modules = Database::sql2array('SELECT * FROM `core_pages_modules`');
		$module_block = array();
		$module_roles = array();
		$pages_modules_prepared = array();
		foreach ($pages_modules as $pm) {

			if ($pm['enabled'] == 1)
				$pages_modules_prepared[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = array(); //;array($modules[$pm['id_module']]['name'] => array());
			else
				$pages_modules_prepared_d[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = $modules[$pm['id_module']]['name'];

			$module_block[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = $pm['block'];
			$module_action[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = $pm['action'];
			$module_mode[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = $pm['mode'];
			$module_params[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = $this->getParamsFromString($pm['params']);
			$module_roles[$pm['id_page']][$modules[$pm['id_module']]['name']][$pm['action']][$pm['mode']] = $this->genRoles(explode(',', $pm['roles']));
		}


		// LibPages.php
		$pagesClass = array();
		$pageParams = array();
		//$blocknames = Database::sql2array('SELECT * FROM `core_blocks`', 'id');
		foreach ($pages as $page) {
			$modules_current = array();
			if (isset($pages_modules_prepared[$page['id']]))
				foreach ($pages_modules_prepared[$page['id']] as $module_name => $moduleSettings) {
					foreach ($moduleSettings as $modaction => $d) {
						foreach ($d as $modmode => $f) {

							//echo '<pre>';
							$modules_current[$module_name][$modaction][$modmode] = $moduleSettings[$modaction][$modmode];
							//$modules_current[$module_name]['block'] = $blocknames[$module_block[$page['id']][$module_name]]['name'];
							if (isset($module_roles[$page['id']][$module_name][$modaction][$modmode]))
								$modules_current[$module_name][$modaction][$modmode]['roles'] = $module_roles[$page['id']][$module_name][$modaction][$modmode];
							$modules_current[$module_name][$modaction][$modmode]['action'] = $module_action[$page['id']][$module_name][$modaction][$modmode];
							$modules_current[$module_name][$modaction][$modmode]['mode'] = $module_mode[$page['id']][$module_name][$modaction][$modmode];
							$modules_current[$module_name][$modaction][$modmode]['params'] = $module_params[$page['id']][$module_name][$modaction][$modmode];
						}
					}
				}

			if ($page['cache_sec']) {
				$pageParams['cache'] = true;
				$pageParams['cache_sec'] = (int) $page['cache_sec'];
			} else {
				$pageParams['cache'] = false;
				$pageParams['cache_sec'] = 0;
			}
			

			$modules_currentx = array();
			if (count($modules_current))
				foreach ($modules_current as $moduleName => $action_)
					foreach ($action_ as $mod => $data) {
						$x = array_pop(array_values($data));
						foreach ($data as $row)
							$modules_currentx[] = array($moduleName => $row);
					}
			//if (count($modules_currentx) && $page['id'] != 8)
				$pagesClass['pages'][$page['name']] = array(
				    'title' => $page['title'],
				    'name' => $page['name'],
				    'params' => $pageParams,
				    'xslt' => $page['xslt'],
				    'modules' => $modules_currentx,
					//'modules_deprecated' => isset($pages_modules_prepared_d[$page['id']]) ? $pages_modules_prepared_d[$page['id']] : array(),
				);
		}

		$phplib_pages_s = '<?php  /* GENERATED AUTOMATICALLY AT ' . date('Y-m-d') . ', DO NOT MODIFY */' . "\n" . 'class LibPages{';
		foreach ($pagesClass as $property => $value) {
			$phplib_pages_s.= "\n" . 'public static $' . $property . ' = ' . "\n";
			$phplib_pages_s.=var_export($value, 1) . ';';
		}
		$phplib_pages_s.="\n" . '}';

		file_put_contents($phplib_pages, $phplib_pages_s);

		// LibModules.php
		$modulesClass = array();
		$moduleParams = array();

		foreach ($modules as $id => $module) {
			if ($module['cache_sec']) {
				$moduleParams['cache_sec'] = (int) $module['cache_sec'];
				$moduleParams['cache'] = true;
				$moduleParams['xHTML'] = (int) $module['xHTML'] ? true : false;
			} else {
				$moduleParams['cache_sec'] = false;
				$moduleParams['cache'] = false;
				$moduleParams['xHTML'] = false;
			}
			$modulesClass['modules'][$module['name']] = array(
			    'name' => $module['name'],
			    'xslt' => $module['xslt'],
			    'params' => $moduleParams,
			);
		}

		$phplib_modules_s = '<?php  /* GENERATED AUTOMATICALLY AT ' . date('Y-m-d') . ', DO NOT MODIFY */' . "\n" . 'class LibModules{';
		foreach ($modulesClass as $property => $value) {
			$phplib_modules_s .= "\n" . 'public static $' . $property . ' = ' . "\n";
			$phplib_modules_s .= var_export($value, 1) . ';';
		}
		$phplib_modules_s.="\n" . '}';
		file_put_contents($phplib_modules, $phplib_modules_s);

		// URI PATHS
		$Map = array();
		$Map['sinonim'] = array();
		foreach ($pages as $page) {
			if ($page['uri_redirect']) {
				$Map['sinonim'][$page['uri_path']] = $page['uri_redirect'];
			} else {
				$Map['map'][$page['uri_path']] = $page['name'];
				//$Map['map'][$page['uri_path']] = $pagesClass['pages'][$page['name']];
				//foreach ($pagesClass['pages'][$page['name']]['modules'] as $moduleName => $data)
				//	$Map['map'][$page['uri_path']]['modules'][$moduleName] = $modulesClass['modules'][$moduleName];
			}
		}
		$phplib_map_s = '<?php  /* GENERATED AUTOMATICALLY AT ' . date('Y-m-d') . ', DO NOT MODIFY */' . "\n" . 'class LibMap{';
		foreach ($Map as $property => $value) {
			$phplib_map_s .= "\n" . 'public static $' . $property . ' = ' . "\n";
			$phplib_map_s .= var_export($value, 1) . ';';
		}
		$phplib_map_s.="\n" . '}';
		file_put_contents($phplib_map, $phplib_map_s);
	}

}