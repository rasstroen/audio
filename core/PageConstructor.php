<?php

/* этот класс пинает всех:
 * собирает xslt шаблоны
 * собирает xml дерево из деревьев модулей
 * собирает xslt шаблон из кусков
 * выполняет трансормацию
 * возвращает готовый HTML
 */

class PageConstructor {

	private $pageName;
	private $pageSettings;
	private $xsltFiles = array();
	private $cssFiles = array();

	function __construct($pageName) {
		$this->pageName = $pageName;
		$this->pageSettings = isset(LibPages::$pages[$this->pageName]) ? LibPages::$pages[$this->pageName] : false;
		if (!$this->pageSettings)
			throw new Exception('No page params in lib for #' . $pageName, Error::E_MODULE_SETTINGS_NOT_FOUND);
		if (!isset($this->pageSettings['xslt']))
			throw new Exception('No page param [xslt] in lib for #' . $pageName, Error::E_MODULE_SETTINGS_NOT_FOUND);
	}

	function getCssFile() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$xsltName = $this->pageSettings['xslt'];
		$cssName = str_replace('.xsl', '', $xsltName);
		return array('file' => Config::need('static_path') . '/' . $current_user->getTheme() . '/css/layouts/' . $cssName . '.css',
		    'path' => Config::need('www_path') . '/static/' . $current_user->getTheme() . '/css/layouts/' . $cssName . '.css');
	}

	function getCssThemeFile() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$xsltName = $this->pageSettings['xslt'];
		$cssName = str_replace('.xsl', '', $xsltName);
		return array('file' => Config::need('static_path') . '/' . $current_user->getTheme() . '/css/layout.css',
		    'path' => Config::need('www_path') . '/static/' . $current_user->getTheme() . '/css/layout.css');
	}

	function getCssThemeResetFile() {
		global $current_user;
		/* @var $current_user CurrentUser */
		$xsltName = $this->pageSettings['xslt'];
		$cssName = str_replace('.xsl', '', $xsltName);
		return array('file' => Config::need('static_path') . '/' . $current_user->getTheme() . '/css/reset.css',
		    'path' => Config::need('www_path') . '/static/' . $current_user->getTheme() . '/css/reset.css');
	}

	private function processModule($moduleName, $additionalSettings = array()) {

		// запускаем модуль
		$action = isset($additionalSettings['action']) ? $additionalSettings['action'] : false;
		$mode = isset($additionalSettings['mode']) ? $additionalSettings['mode'] : false;
		if (isset(LibModules::$modules[$moduleName]))
			eval('$module = new ' . $moduleName . '_module($moduleName, $additionalSettings , $action , $mode);');
		else
			throw new Exception('module ' . $moduleName . ' missed in modules library', Error::E_MODULE_NOT_FOUND);
		/* @var $module BaseModule */
		// получаем xml от модуля
		Log::timing($moduleName . ' : processModule');
		$module->process();
		Log::timing($moduleName . ' : processModule');
		$xmlNode = $module->getResultXML();


		// добавляем xsl файл в список
		$xsltFileName = $module->getXSLTFileName();
		if ($css = $module->getCssFile())
			$this->cssFiles[$css['file']] = $css['path'];

		if ($xsltFileName)
			$this->addXsltFile($moduleName, $xsltFileName, $action, $mode);
		else if ($xsltFileName == null)
			$this->addXsltNullFile($moduleName, $action, $mode);

		if ($xmlNode !== false) {
			XMLClass::setNodeProps(XMLClass::appendNode($xmlNode, $moduleName), $module->getProps());
		}
	}

	private function appendCss() {
		$cssFiles = array();
		// css for reset
		$css = $this->getCssThemeResetFile();
		$cssFiles[$css['file']] = $css['path'];
		// css for current theme
		$css = $this->getCssThemeFile();
		$cssFiles[$css['file']] = $css['path'];
		// css for current layout
		$css = $this->getCssFile();
		$cssFiles[$css['file']] = $css['path'];
		foreach ($this->cssFiles as $a => $b) {
			$cssFiles[$a] = $b;
		}


		foreach ($cssFiles as $cssFilePath => $cssFileName) {
			if (file_exists($cssFilePath)) {
				$cssNode = XMLClass::$xml->createElement('css');
				$cssNode->setAttribute('path', $cssFileName);
				XMLClass::$pageNode->appendChild($cssNode);
			}
		}
	}

	public function process() {
		global $current_user;
		/* @var $current_user CurrentUser */
		XMLClass::$pageNode = XMLClass::createNodeFromObject($this->pageSettings, false, 'page', false);
		XMLClass::appendNode(XMLClass::$pageNode, $this->pageName);
		XMLClass::$pageNode->setAttribute('current_url', Request::$url);
		XMLClass::$pageNode->setAttribute('page_url', Config::need('www_path') . '/' . Request::$pageName . '/');
		XMLClass::$pageNode->setAttribute('prefix', Config::need('www_path') . '/');
		if ($current_user->authorized)
			XMLClass::$CurrentUserNode = XMLClass::createNodeFromObject($current_user->getXMLInfo(), false, 'current_user', false);
		else
			XMLClass::$CurrentUserNode = XMLClass::createNodeFromObject(array(), false, 'current_user', false);
		XMLClass::$pageNode->appendChild(XMLClass::$CurrentUserNode);
		// втыкаем модули страницы
		$role = $current_user->getRole();

		// авторизация - везде
		$this->pageSettings['modules'][] =
			array(
			    'users' =>
			    array(
				'roles' =>
				array(
				    '' => '',
				),
				'action' => 'show',
				'mode' => 'auth',
				'params' =>
				array(
				),
			    ),
		);
		if (isset($this->pageSettings['modules']) && is_array($this->pageSettings['modules'])) {
			foreach ($this->pageSettings['modules'] as $fid => $module) {
				foreach ($module as $moduleName => $additionalSettings) {
					if (!isset($additionalSettings['roles'][$role]))
						$this->processModule($moduleName, $additionalSettings);
				}
			}
		}
		$this->appendCss();
		// xml дерево создано, теперь генерируем xslt шаблон
		// выдаем html
		//Request::$responseType = 'xml';
		switch (Request::$responseType) {
			case 'xml':case 'xmlc':
				return XMLClass::dumpToBrowser();
				break;
			case 'xsl':case 'xslc':
				$xslTemplateClass = new XSLClass($this->pageSettings['xslt']);
				$xslTemplateClass->setTemplates($this->xsltFiles);
				return $xslTemplateClass->dumpToBrowser();
				break;
			case 'html':
				$xslTemplateClass = new XSLClass($this->pageSettings['xslt']);
				$xslTemplateClass->setTemplates($this->xsltFiles);
				$html = $xslTemplateClass->getHTML(XMLClass::$xml);
				if ($xslTemplateClass->fetched_from_cache) {
					// чтобы знать, что файл из кеша
					Log::logHtml('xslt template GOT from cache');
				}
				if ($xslTemplateClass->puted_into_cache) {
					// чтобы знать, что файл из кеша
					Log::logHtml('xslt template PUT to cache');
				}

				return $html;
				break;
			default:
				return XMLClass::dumpToBrowser();
				break;
		}
	}

	//-----------
	// добавляем шаблон модуля в список шаблонов страницы
	private function addXsltFile($moduleName, $xsltFileName, $action, $mode) {
		$this->xsltFiles[$moduleName][] = array(
		    'view' => $xsltFileName,
		    'action' => $action,
		    'mode' => $mode
		);
	}

	private function addXsltNullFile($moduleName) {
		$this->xsltFiles[$moduleName] = 'null';
	}

}