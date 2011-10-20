<?php

$root = Config::need('base_path');
require_once $root.'/functions/functions.php';

$includePathes = array(
    $root,
    $root . 'core',
    $root . 'modules',
    $root . 'modules/write',
    $root . 'jmodules',
    $root . 'classes',
    $root . 'classes/User',
    $root . 'functions',
    $root . 'phplib',
    
);

set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $includePathes));

function __autoload($className) {
	require_once($className . '.php');
}