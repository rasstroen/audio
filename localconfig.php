<?php
/**
 * накладываем этот конфиг на конфиг ядра
 * всё, что находится в $local_config, перепишет конфиг ядра.
 * этот файл не должен быть в репозитории
 */

$local_config = array(
    'base_path' => '/home/audio/', // в какой директории на сервере лежит index.php
    'www_absolute_path' => '', // например для http://localhost/hello/ это будет /hello
    'www_path' => 'http://audio.ownspace.ru',
    'www_domain' => 'audio.ownspace.ru',
    'default_page_name' => 'main', // синоним для корня сайта
    'static_path' => '/home/audio/static', // 
    //USERS
    'default_language' => 'ru',
    //Register
    'register_email_from' => 'amuhc@yandex.ru',
    //Auth
    'auth_cookie_lifetime' => 360000,
    'auth_cookie_hash_name' => 'audhash_',
    'auth_cookie_id_name' => 'audid_',
    // Avatars
    'avatar_upload_path' => '/home/audio/static/upload/avatars', // в какой директории на сервере лежит index.php
    // MySQL
    'dbuser' => 'root',
    'dbpass' => '2912',
    'dbhost' => 'localhost',
    'dbname' => 'ls2',
    // MODULES
    'writemodules_path' => '/home/audio/modules/write',
    // THEMES
    'default_theme' => 'default',
    // XSLT
    'xslt_files_path' => '/home/audio/xslt',
    //CACHE
    'cache_enabled' => false, // отключить/включить весь кеш
    'cache_default_folder' => '/home/audio/cache/var',
    // XSL CACHE
    'xsl_cache_min_sec' => 1,
    'xsl_cache_max_sec' => 300,
    'xsl_cache_file_path' => '/home/audio/cache/xsl',
    'xsl_cache_memcache_enabled' => false,
    'xsl_cache_xcache_enabled' => true,
    // XML CACHE
    'xml_cache_min_sec' => 1,
    'xml_cache_max_sec' => 86400,
    'xml_cache_file_path' => '/home/audio/cache/xml',
    'xml_cache_memcache_enabled' => false,
    'xml_cache_xcache_enabled' => true,
    // ADMIN
    'phplib_pages_path' => '/home/audio/phplib',
    'phplib_modules_path' => '/home/audio/phplib',
    // BOOK FILES
    'files_path' => '.\\files',
    // HARDCODE
   
   
);