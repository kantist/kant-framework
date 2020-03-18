<?php
// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$config->load('default');
$config->load($application_config);
$registry->set('config', $config);

// Request
$registry->set('request', new Request());

if ($config->get('error_log')) {
	$registry->set('log', new Log($config->get('error_filename')));	
}

date_default_timezone_set($config->get('timezone_default'));

// Response
$response = new Response();
$response->addHeader($config->get('response_header')[0]);
$response->setCompression($config->get('response_compression'));
$registry->set('response', $response);

// Database
if ($config->get('db_autostart')) {
	$registry->set('db', new DB($config->get('db_type'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port')));
}

// Session
if ($config->get('session_autostart')) {
	$session = new Session($config->get('session_engine'), $registry);
	$registry->set('session', $session);

	if (isset($_COOKIE[$config->get('session_name')])) {
		$session_id = $_COOKIE[$config->get('session_name')];
	} else {
		$session_id = '';
	}

	$session->start($session_id);

	setcookie($config->get('session_name'), $session->getId(), time()+ini_get('session.cookie_lifetime'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
}

// Cache
if ($config->get('cache_autostart')) {
	$registry->set('cache', new Cache($config->get('cache_type'), $config->get('cache_expire')));
}

// Event
$event = new Event($registry);
$registry->set('event', $event);

// API Params
if ($config->get('param_autostart')) {
	$registry->set('param', new Param($registry));
}

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		$event->register($key, new Action($value));
	}
}

// Config Autoload
if ($config->has('config_autoload')) {
	foreach ($config->get('config_autoload') as $value) {
		$loader->config($value);
	}
}

// Library Autoload
if ($config->has('library_autoload')) {
	foreach ($config->get('library_autoload') as $value) {
		$loader->library($value);
	}
}

// Model Autoload
if ($config->has('model_autoload')) {
	foreach ($config->get('model_autoload') as $value) {
		$loader->model($value);
	}
}

// Helper Autoload
if ($config->has('helper_autoload')) {
	foreach ($config->get('helper_autoload') as $value) {
		$loader->model($value);
	}
}

// Front Controller
$controller = new Front($registry);

// Pre Actions
if ($config->has('action_pre_action')) {
	foreach ($config->get('action_pre_action') as $value) {
		$controller->addPreAction(new Action($value));
	}
}

// Dispatch
$controller->dispatch(new Action($config->get('action_router')), new Action($config->get('action_error')));

// Output
$response->output();