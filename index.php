<?php
// Version
define('VERSION', '1.0.0');

// BASES
define('DOCUMENT_ROOT', realpath(dirname(__FILE__))); // ../../sub.domain.com
define('DOCUMENT_PATH', end(explode('/', DOCUMENT_ROOT))); //
define('HTTP_SERVER', 'https://' . $_SERVER['HTTP_HOST'] . '/' . DOCUMENT_PATH . '/'); // https://sub.domain.com/v1/

// COMMON DIR
define('DIR_APPLICATION', DOCUMENT_ROOT . '/');
define('DIR_SYSTEM', DIR_APPLICATION . 'system/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_REPOSITORY', DIR_APPLICATION . 'repository/');
define('DIR_IMAGE', DIR_REPOSITORY . 'repo/image/');
define('DIR_CACHE', DIR_REPOSITORY . 'repo/cache/');
define('DIR_LOGS', DIR_REPOSITORY . 'repo/logs/');

// OTHER DEFINES
define('ENVIRONMENT', 'development');
define('COOKIE_DOMAIN', '.' . MAIN_DOMAIN);
define('SESSION', '.' . strtoupper(MAIN_DOMAIN));
define('SESSION_EXPIRE', 2 * 24 * 60 * 60);

// Set Error Display
if (ENVIRONMENT == 'development') {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

$application_config = 'api';

// Application
require_once(DIR_SYSTEM . 'framework.php');