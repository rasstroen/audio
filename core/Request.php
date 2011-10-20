<?php

/* Обработка входных параметров скрипта
 *
 * Разбирает POST/GET параметры, определяет, какую страницу запрашивает пользователь, преобразует
 * URI в набор параметров для страницы, вырезает специальные параметры (serxml,serxsl).
 * Обработка входных параметров для каждого модуля также происходит в лассе Request
 *
 * @author rasstroen
 *
 */

class Request {

	private static $initialized = false;
	public static $get = array();
	public static $post = array();
	public static $pageName = 'p404';
	public static $url = '';
	public static $responseType = 'html';

	/** обрабатываем входные параметры скрипта, определяем запрашиваемую страницу
	 *
	 */
	public static function initialize() {
		if (self::$initialized)
			return;
		self::$initialized = true;
		// принимаем uri
		$e = explode('?', $_SERVER['REQUEST_URI']);
		$_SERVER['REQUEST_URI'] = $e[0];
		if (isset($e[1]))
			parse_str($e[1], $d);
		else
			$d = array();
		$prepared_get = array();
		foreach ($d as $name => $val) {
			echo $val . "\n";
			$prepared_get[urldecode($name)] = urldecode($val);
			if (urldecode($val) != $val)
				die($val . 's');
			$val = iconv('CP1251', 'UTF-8', $val);
		}
		self::$get = $prepared_get;
		$path_array = explode('/', self::processRuri($_SERVER['REQUEST_URI']));
		// убиваем начальный слеш
		array_shift($path_array);
		// определяем, что из этого uri является страницей
		self::$pageName = self::getPage($path_array);
		//die(self::$pageName);
		// разбираем параметры
		self::parse_parameters($path_array);

		foreach ($_POST as $f => $v) {
			self::$post[$f] = $v;
		}
		unset($_POST);
		unset($_GET);
	}

	/** по маске проверяем параметры для модуля
	 *
	 *
	 * @param array $mask - маска array('int','string','*int'), * - параметр необязателен
	 * @param array $data - массив входных данных
	 *
	 * @return array массив значений, если они соответствуют маске.
	 * "*" значения возвращаются как null если во входном массиве их не оказалось. или FALSE в случае,
	 * если вхордные параметры не соответствуют маске
	 */
	public static function checkParameters(array $data, array $mask) {
		$params = array();
		$i = 0;
		foreach ($mask as $field => $type) {
			$value = isset($data[$i]) ? $data[$i] : null;
			$params[$field] = self::checkValue($value, $type);
			if (is_null($params[$field]))
				throw new Exception('Required get field #' . $field . ' missed[' . $i . ']');
			$i++;
		}
		return $params;
	}

	/*
	 * отдает имя поля, по которому
	 */

	public static function checkPostParameters(array $mask) {
		$params = array();
		foreach ($mask as $field => $type) {
			$value = self::post($field, null);
			if (is_null($value) && !isset($type['*']))
				throw new Exception('Required post field ' . $field . ' missed');
			$params[$field] = self::checkValue($value, $type);
		}
		return $params;
	}

	public function checkValue($value, $type) {
		$min_length = 0;
		$max_length = 0;
		$regexp = false;
		$optional = false;
		if (is_array($type)) {
			$min_length = isset($type['min_length']) ? (int) $type['min_length'] : 0;
			$max_length = isset($type['max_length']) ? (int) $type['max_length'] : 0;
			$regexp = isset($type['regexp']) ? $type['regexp'] : false;
			$optional = isset($type['*']) ? true : false;
			$type = $type['type'];
		}
		switch ($type) {
			case 'email':
				if (!valid_email_address($value))
					return false;
				break;
			case 'string':
				if (!$value && $optional)
					return '';
				if (!$value)
					return false;
				if ($min_length)
					if (mb_strlen(trim($value), 'UTF-8') < $min_length)
						return false;
				if ($max_length)
					if (mb_strlen(trim($value), 'UTF-8') > $max_length)
						return false;
				if ($regexp)
					if (!preg_match($regexp, $value))
						return false;
				break;
			case 'int':
				if ($value == null) {
					if ($optional)
						return false;
				}
				if (!is_numeric($value)) {
					$value = (int) $value;
				}
				break;
			case '':
				break;
		}
		return $value;
	}

	/*
	 *
	 */

	public static function getAllParameters() {
		self::initialize();
		return self::$get;
	}

	/*
	 *
	 */

	public static function get($offset, $default = false) {
		self::initialize();
		if (isset(self::$get[$offset]))
			return self::$get[$offset];
		return $default;
	}

	/*
	 *
	 */

	public static function post($name, $default = false) {
		return isset(self::$post[$name]) ? self::$post[$name] : $default;
	}

	/*
	 *
	 */

	private static function set($offset, $value) {
		self::$get[$offset] = $value;
	}

	/*
	 *
	 */

	private static function parse_parameters(array $path_array) {
		$s = array_shift($path_array);
		self::$url = self::specialParameters($s) ? $s . '/' : '';
		$i = 0;
		foreach ($path_array as $value) {
			$value = self::specialParameters($value); // если это служебное, изымаем из параметров
			if ($value) {
				self::$url.=urldecode($value) . '/';
				self::set($i++, $value);
			}
		}
		self::$url = Config::need('www_path') . '/' . self::$url;
	}

	/*
	 *
	 */

	private static function specialParameters($value) {
		switch ($value) {
			case 'serxml':
				// мы хотим получить контент в xml
				self::$responseType = 'xml';
				$value = false;
				break;
			case 'serxsl':
				// хотим посмотреть xsl шаблон
				self::$responseType = 'xsl';
				$value = false;
				break;
			case 'serxmlc':
				// мы хотим получить контент в xml
				self::$responseType = 'xmlc';
				$value = false;
				break;
			case 'serxslc':
				// хотим посмотреть xsl шаблон
				self::$responseType = 'xslc';
				$value = false;
				break;
			case 'logout':
				// выходим
				$value = false;
				if (!isset($_POST['writemodule']))
					$_POST['writemodule'] = 'LogoutWriteModule';
				break;
			case 'emailconfirm': // на эту страницу попадают из письма, соответственно нужно
				// специально указать, что будет использоваться модуль записи
				if (!isset($_POST['writemodule']))
					$_POST['writemodule'] = 'EmailConfirmWriteModule';
				break;
		}
		return $value;
	}

	/*
	 *
	 */

	// обрабатываем строку запрос в соотв с настройками (корень сайта не в корне домена и т.п.)
	private static function processRuri($uri) {
		$root = Config::need('www_absolute_path', false);
		if ($root) {
			$uri = str_replace($root, '', $uri);
		}
		return $uri;
	}

	/*
	 *
	 */

	private static function getPage(array $path) {

		$parts = array();
		foreach ($path as $path_part) {
			$part = '';
			if ($path_part && self::specialParameters($path_part)) {
				if (is_numeric($path_part)) {
					$part = '%d';
				} else {
					$part = $path_part;
				}
				$parts[] = $part;
			}
		}
		$path_mapped = implode('/', $parts);


		if (!isset(LibMap::$map[$path_mapped])) {
			$path_mapped_reduce = $parts;
			for ($i = count($parts); $i > -1; $i--) {
				unset($path_mapped_reduce[$i]);

				// path with *
				$reduced_uri = implode('/', $path_mapped_reduce) . '/*';
				if ($pageName = self::getPageByPath($reduced_uri))
					return $pageName;
				// path with %s
				$reduced_uri = implode('/', $path_mapped_reduce) . '/%s';
				if ($pageName = self::getPageByPath($reduced_uri))
					return $pageName;
				// path
				$reduced_uri = implode('/', $path_mapped_reduce);
				if ($pageName = self::getPageByPath($reduced_uri))
					return $pageName;
			}
		} else {
			return LibMap::$map[$path_mapped];
		}
		return 'p404';
	}

	private static function getPageByPath($path) {
		$path = $path ? $path : '/';
		echo $path . '<br/>';
		if (isset(LibMap::$sinonim[$path])) {
			return LibMap::$map[LibMap::$sinonim[$path]];
		}
		if (isset(LibMap::$map[$path])) {
			return LibMap::$map[$path];
		}
		return false;
	}

	public static function headerCookie($name, $value, $expire, $path, $domain, $secure, $httponly) {
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}

}