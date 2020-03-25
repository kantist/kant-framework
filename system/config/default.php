<?php
// Site
$_['site_ssl']             = false;

// Locale
$_['locale_default']	   = 'tr_TR.utf8';
$_['timezone_default']	   = 'Asia/Istanbul';

// Database
$_['db_autostart']         = false;
$_['db_type']              = 'pdo'; // pdo or mysqli
$_['db_hostname']          = 'localhost';
$_['db_username']          = 'root';
$_['db_password']          = '';
$_['db_database']          = '';
$_['db_port']              = 3306;

// Cache
$_['cache_autostart']	   = true;
$_['cache_type']           = 'file'; // apc, file or mem
$_['cache_expire']         = 2 * 24 * 60 * 60;

// Session
$_['session_autostart']    = true;
$_['session_engine']       = 'db';
$_['session_name']         = 'KANTSESSID';

// Sms
$_['sms_autostart']			= false;
$_['sms_type']				= 'IletiMerkezi'; // IletiMerkezi or AsistIletisim
$_['sms_params']			= array();

// Template
$_['template_type']        = 'basic';

// Param
$_['param_autostart']      = false;

// Error
$_['error_display']        = true;
$_['error_log']            = true;
$_['error_filename']       = 'error.log';

// Reponse
$_['response_header']      = array('Content-Type: text/html; charset=utf-8');
$_['response_compression'] = 0;

// Autoload Configs
$_['config_autoload']      = array();

// Autoload Libraries
$_['library_autoload']     = array();

// Autoload Models
$_['model_autoload']       = array();

// Autoload Helpers
$_['helper_autoload']       = array();

// Actions
$_['action_default']       = 'error/route';
$_['action_router']        = 'startup/router';
$_['action_error']         = 'error/route';
$_['action_pre_action']    = array();
$_['action_event']         = array();