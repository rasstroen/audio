<?php

ini_set('upload_max_filesize', '15M');

ini_set('mbstring.substitute_character', "none");
$dev_mode = true;

$project_name = 'ls2.0';
ini_set('display_errors', $dev_mode ? 1 : 0);
error_reporting(E_ALL);

function shutdown_handler() {
	global $dev_mode, $errorString, $errorDescription, $errorCode;
	$e = error_get_last();
	if ($e['type'] == 1) {  // фатальная ошибка
		if ($dev_mode) {
			$errorString = $e['message'];
			$errorDescription = '[' . $e['file'] . ':' . $e['line'] . ']';
		} else {
			$errorDescription = '';
			$errorString = $e['message'];
		}
		$errorCode = 0;
		XMLClass::reinitialize();
		$page = new PageConstructor('errors/p502.xml');
		@ob_end_clean();
		echo $page->process();
	}
}

register_shutdown_function('shutdown_handler');
// инклудим
require_once 'config.php';
if (file_exists('localconfig.php'))
	require_once 'localconfig.php';
else
	$local_config = array();
// переписываем конфиг
Config::init($local_config);
require_once 'include.php';

//jQuery запросы
if (isset($_POST['jquery'])) {
	if (is_string($_POST['jquery'])) {
		$jModuleName = 'J' . $_POST['jquery'];
		$jModule = new $jModuleName;
		echo $jModule->getJson();
	}
	if ($dev_mode) {
		//echo "\r\n\r\n\n".Log::getHtmlLog();
	}
	exit();
}

Log::timing('total');
try {
	ob_start();
	// разбираем запрос
	Request::initialize();
	// авторизуем пользователя
	$current_user = new CurrentUser();
	// выполняем модули записи, если был соответствующий POST запрос
	if (Request::post('writemodule')) {
		PostWrite::process(Request::post('writemodule'));
	}
	// запускаем обработку страницы
	$page = new PageConstructor(Request::$structureFile);
	@ob_end_clean();
	echo $page->process();
} catch (Exception $e) {
	if ($dev_mode) {
		$errorString = $e->getMessage();
		$errorDescription = '[' . $e->getFile() . ':' . $e->getLine() . '][' . $e->getCode() . ']';
		$errorDescription .= '<br/><pre>' . $e->getTraceAsString() . '</pre>';
	} else {
		$errorDescription = '';
		$errorString = $e->getMessage();
	}
	$errorCode = $e->getCode();
	XMLClass::reinitialize();
	StructureParser::clear();
	$page = new PageConstructor('errors/p502.xml');
	@ob_end_clean();
	echo $page->process();
}

Log::timing('total');
if ($dev_mode) {
	echo Log::getHtmlLog();
}



	