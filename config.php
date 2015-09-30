<?php 

//define('APP_PATH', '/Users/Juank/Sites/ApiGenerator/');
//define('APP_ROOT', '/ApiGenerator/public/');

//define('CACHE_PATH', '/Users/Juank/Sites/ApiGenerator/cache/');
//define('UPLOADS_PATH', '/Users/Juank/Sites/ApiGenerator/public/uploads/');
//define('LOG_PATH', '/Users/Juank/Sites/ApiGenerator/logs/');

//define('RESOURCES_PATH', '/Users/Juank/Sites/ApiGenerator/logs/');
//define('DEVELOPING', true);

return array(
	'rocket' => array(
		/*'context_index_file' => APP_PATH . 'api/resources/contexts.php',
		'routes_file' => APP_PATH . 'api/resources/routes.php',
		'contexts_file' => APP_PATH . 'api/contexts/contexts.php',
		'delegates_file' => APP_PATH . 'api/delegates/delegates.php',
		'resources_path' => APP_PATH . 'api/resources/resources/'*/
	),
	'request' => array(
		//'app_root' => APP_ROOT, // path from server root to app root
		'default_lang' => 'en_US'
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
		'payload' => 'emails/'
	),
	'translator' => array(
		'payload' => 'lang/'
	),
	'user' => array(
		
	),
	'api' => array(
		'spec_file' => 'spec/spec.json',
		'ignored_tables' => array('some_table')
	)
);