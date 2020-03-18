<?php
// Site
$_['site_ssl']				= true;

// Database
$_['db_autostart']			= true;
$_['db_type']				= 'mysqli';
$_['db_hostname']			= 'localhost';
$_['db_username']			= 'db_user';
$_['db_password']			= 'db_pass';
$_['db_database']			= 'db_database';
$_['db_port']				= '3306';

// Reponse
$_['response_header']      = array('Content-Type: application/json');

// Session
$_['session_autostart']		= false;

// Cache
$_['cache_autostart']		= false;
$_['cache_type']			= 'file';
$_['cache_expire']			= 2 * 24 * 60 * 60;

// Param
$_['param_autostart']		= true;

// Error
$_['error_log']				= true;
if (ENVIRONMENT == 'production') {
	$_['error_display']			= false;
}

// Actions
$_['action_pre_action']		= array(
	'startup/config',
	'startup/startup',
	'startup/error',
	'startup/seo_url'
);