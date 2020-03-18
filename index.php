<?php
// Version
define('VERSION', '1.0.0');
define('VERSION_PATH', 'v1');

// BASES
define('DOMAIN', explode('.', $_SERVER['HTTP_HOST'])[0]); // sub
define('MAIN_DOMAIN', str_replace(DOMAIN . '.', '', $_SERVER['HTTP_HOST'])); // domain.com
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']); // ../../sub.domain.com
define('HTTP_SERVER', 'https://' . $_SERVER['HTTP_HOST'] . '/' . VERSION_PATH . '/'); // https://sub.domain.com/v1/

// COMMON DIR
define('DIR_APPLICATION', DOCUMENT_ROOT . '/');
define('DIR_SYSTEM', DIR_APPLICATION . 'system/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LOGS', DOCUMENT_ROOT . '/');
define('DIR_REPOSITORY', DIR_APPLICATION . 'repository/');
define('DIR_IMAGE', DIR_REPOSITORY . 'repo/image/');
define('DIR_CACHE', DIR_REPOSITORY . 'repo/cache/');

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