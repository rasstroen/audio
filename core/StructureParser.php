<?php

class StructureParser {

	private static $modules;
	private static $data;

	public static function clear() {
		self::$modules = array();
		self::$data = array();
	}

	public static function XMLToArray($path_to_structure, $path_to_default) {
		self::parse($path_to_default);
		self::parse($path_to_structure);
		if(!self::getLayoutPath())
			throw new Exception('missed layout header in structure file '.$path_to_structure);
	}

	private static function parse($path) {
		$path = trim($path);
		$libmodules = array();
		if (file_exists($path))
			$structure = simpleXMLToArray(simplexml_load_file($path));
		else
			throw new Exception('[' . $path . '] cant be loaded');
		
		foreach ($structure['data'] as $f => $v) {
			if ($f == 'stylesheet') {
				if (!isset($v['path'])) {
					foreach ($v as $stylesheet) {
						self::$data['stylesheet'][] = $stylesheet;
					}
				}else
					self::$data['stylesheet'][] = $v;
			}else
				self::$data[$f] = $v;
		}

		if (isset($structure['data']['role']['need']))
			Error::CheckThrowAuth($data['data']['role']['need']);
		// вынимаем модули
		$i = 0;
		foreach ($structure['blocks'] as $container => &$data) {
			if (isset($data['module']['name'])) {
				$data['module'] = array($data['module']);
			}
			foreach ($data as $modules) {
				foreach ($modules as $module) {
					$i++;
					$libmodules[$i] = $module;
					$libmodules[$i]['container'] = $container;
					unset($libmodules[$i]['param']);
					if (isset($module['param']))
						$libmodules[$i]['params'] = isset($module['param']['name']) ? array($module['param']) : $module['param'];
				}
			}
		}
		foreach ($libmodules as $module)
			self::$modules[] = $module;
	}

	public static function toXML() {
		$node = XMLClass::$xml->createElement('structure');
		$data = XMLClass::$xml->createElement('data');
		$blocks = XMLClass::$xml->createElement('blocks');

		$content_block = XMLClass::$xml->createElement('content');
		$blocks->appendChild($content_block);

		$sidebar_block = XMLClass::$xml->createElement('sidebar');
		$blocks->appendChild($sidebar_block);

		$header_block = XMLClass::$xml->createElement('header');
		$blocks->appendChild($header_block);

		$node->appendChild($data);
		$node->appendChild($blocks);

		foreach (self::$data as $field => $value) {
			switch ($field) {
				case 'title':
					$Node = XMLClass::$xml->createElement($field);
					$Text = XMLClass::$xml->createTextNode($value);
					$Node->appendChild($Text);
					$data->appendChild($Node);
					break;
				case 'stylesheet':
					foreach ($value as $item) {
						$Node = XMLClass::$xml->createElement($field);
						foreach ($item as $f => $v)
							$Node->setAttribute($f, $v);
						$data->appendChild($Node);
					}
					break;
				default:
					$Node = XMLClass::$xml->createElement($field);
					foreach ($value as $f => $v)
						$Node->setAttribute($f, $v);
					$data->appendChild($Node);
					break;
			}
		}
		foreach (self::$modules as $module) {
			$Node = XMLClass::$xml->createElement('module');
			foreach ($module as $field => $value) {
				if (is_string($value))
					$Node->setAttribute($field, $value);
			}
			switch ($module['container']) {
				case 'content':
					$content_block->appendChild($Node);
					break;
				case 'header':
					$header_block->appendChild($Node);
					break;
				default:
					$sidebar_block->appendChild($Node);
					break;
			}
		}
		return $node;
	}

	public static function getLayoutPath() {
		return self::$data['layout']['file'];
	}

	public static function getModules() {
		return self::$modules;
	}
	
	public static function getTitle() {
		return isset(self::$data['title'])?self::$data['title']:'';
	}

}
