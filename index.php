<?php

$dev_mode = true;
$project_name = 'diary';
ini_set('display_errors', $dev_mode ? 1 : 0);
error_reporting(E_ALL);


if (file_exists('localconfig.php'))
    require_once 'localconfig.php';
else
    $local_config = array();

// инклудим
require_once 'config.php';
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
    exit();
}

Log::timing('total');
try {
    ob_start();
    // разбираем запрос
    $pageName = Request::initialize();
    // авторизуем пользователя
    $current_user = new CurrentUser();
    // выполняем модули записи, если был соответствующий POST запрос
    if (Request::post('writemodule')) {
        PostWrite::process(Request::post('writemodule'));
    }
    // запускаем обработку страницы
    $page = new PageConstructor(Request::$pageName);
    @ob_end_clean();
    echo $page->process();
} catch (Exception $e) {
    if ($dev_mode) {
        $errorString = "<h3>" . $e->getMessage() . '</h3><br/>[' . $e->getFile() . ':' . $e->getLine() . '][' . $e->getCode() . ']';
        $errorString .= '<br/><pre>' . $e->getTraceAsString() . '</pre>';
	die($errorString);
    } else {
        $errorString = $e->getMessage();
    }
    $errorCode = $e->getCode();
    XMLClass::reinitialize();
    $page = new PageConstructor('p502');
    @ob_end_clean();
    echo $page->process();
}

Log::timing('total');
if ($dev_mode) {
    echo Log::getHtmlLog();
}




