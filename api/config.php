<?php 

define('APP_PATH', '/Users/Juank/Sites/ApiGenerator/');
define('APP_ROOT', '/ApiGenerator/public/');
//define('CACHE_PATH', '/Users/Juank/Sites/ApiGenerator/cache/');
//define('UPLOADS_PATH', '/Users/Juank/Sites/ApiGenerator/public/uploads/');
//define('LOG_PATH', '/Users/Juank/Sites/ApiGenerator/logs/');

define('RESOURCES_PATH', '/Users/Juank/Sites/ApiGenerator/logs/');

return array(
	'rocket' => array(
		'context_index_file' => APP_PATH . 'api/resources/contexts.php',
		'routes_file' => APP_PATH . 'api/resources/routes.php',
		'contexts_file' => APP_PATH . 'api/contexts/contexts.php',
		'delegates_file' => APP_PATH . 'api/delegates/delegates.php',
		'resources_path' => APP_PATH . 'api/resources/resources/'
	),
	'request' => array(
		'app_path' => APP_PATH, // path to app root folder in filesystem
		'app_root' => APP_ROOT // path from server root to app root
	),
	'response' => array(
		
	),
	'database' => array(
		'driver' => 'mysql',
		'host' => '127.0.0.1',
		'name' => 'generator',
		'username' => 'jcestela',
		'password' => 'tortuga',
		'port' => 3306,
		'timezone' => null
	),
	'error' => array(
		'show_errors' => true,
		'log_errors' => true
	),
	'mail' => array(
		'payload' => APP_PATH . 'emails/'
	),
	'translator' => array(
		'payload' => APP_PATH . 'api/lang/'
	),
	'user' => array(
		
	)
);