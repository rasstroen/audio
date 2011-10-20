<?php
/**
 * накладываем этот конфиг на конфиг ядра
 * всё, что находится в $local_config, перепишет конфиг ядра.
 * этот файл не должен быть в репозитории
 */

$local_config = array(
    'base_path' => './', // в какой директории на сервере лежит index.php
    'www_absolute_path' => '/git/librusec2', // например для http://localhost/hello/ это будет /hello
    'www_path' => 'http://10.0.2.97/git/librusec2',
    'www_domain' => '10.0.2.97',
    'default_page_name' => 'main', // синоним для корня сайта
    'static_path' => './static', // 
    //USERS
    'default_language' => 'ru',
    //Register
    'register_email_from' => 'amuhc@yandex.ru',
    //Auth
    'auth_cookie_lifetime' => 360000,
    'auth_cookie_hash_name' => 'ls2hash_',
    'auth_cookie_id_name' => 'ls2id_',
    // Avatars
    'avatar_upload_path' => './static/upload/avatars', // в какой директории на сервере лежит index.php
    // MySQL
    'dbuser' => 'root',
    'dbpass' => '2912',
    'dbhost' => 'localhost',
    'dbname' => 'ls2',
    // MODULES
    'writemodules_path' => './modules/write',
    // THEMES
    'default_theme' => 'default',
    // XSLT
    'xslt_files_path' => './xslt',
    //CACHE
    'cache_enabled' => false, // отключить/включить весь кеш
    'cache_default_folder' => './cache/var',
    // XSL CACHE
    'xsl_cache_min_sec' => 1,
    'xsl_cache_max_sec' => 300,
    'xsl_cache_file_path' => './cache/xsl',
    'xsl_cache_memcache_enabled' => false,
    'xsl_cache_xcache_enabled' => true,
    // XML CACHE
    'xml_cache_min_sec' => 1,
    'xml_cache_max_sec' => 86400,
    'xml_cache_file_path' => './cache/xml',
    'xml_cache_memcache_enabled' => false,
    'xml_cache_xcache_enabled' => true,
    // ADMIN
    'phplib_pages_path' => './phplib',
    'phplib_modules_path' => './phplib',
    // BOOK FILES
    'files_path' => '.\\files',
    // HARDCODE
   
   
);