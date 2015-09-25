<?php
/**
 * TODO
 * - detect lang on request class, accept headers and override lang param
 * * execute all contexts one time and keep state
 * - cleanup and reorder rocket.php code
 * - debugging global to control if exceptions are propagated or not
 * - build hooks/delegates logic
 * - generator script
 * - documentator script
 * - sync db
 * - cleanup config/folder structure/composer project
 * - datatypes: date, numbers, float, int, relations
 * - traits paged, etc
 * - exposed
 *
 * Roadmap
 * - yaml
 * - handle caching
 * - handle throttling
 * - CSRF
 */


// test for httpS
$protocol = 'http';
if (isset($_SERVER['SERVER_PROTOCOL'])){
	$protocol = strtolower(explode('/', $_SERVER['SERVER_PROTOCOL'])[0]);
}
$protocol .= '://';

$name = '';
if (isset($_SERVER['SERVER_NAME'])){
	$name = $_SERVER['SERVER_NAME'];
}

$port = '';
if (isset($_SERVER['SERVER_PORT'])){
	if ($_SERVER['SERVER_PORT'] != 80){
		$port = ':'.$_SERVER['SERVER_PORT'];
	}
}

$host = $protocol . $name . $port;
define('APP_URL', $host . APP_ROOT);

$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
define('CURRENT_URL', str_replace(APP_ROOT, $uri, APP_URL));


include 'rocket/src/Rocket.php';
include 'database/src/System.php';
include 'mail/src/System.php';
include 'error/src/System.php';
include 'translator/src/System.php';

	//error_reporting(E_ALL);

	// timezone
	date_default_timezone_set('UTC');
	// Encoding
	if (function_exists('mb_get_info')){
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');
		mb_http_input('UTF-8');
		mb_language('uni');
		mb_regex_encoding('UTF-8');
	}
	// CSRF
	// translation

$request = Rocket::set('request', new \Rocket\Request($config['request']));
$response = Rocket::set('response', new \Rocket\Response($config['response']));
$database = Rocket::set('database', new \Rocket\Database\System($config['database']));
$mail = Rocket::set('mail', new \Rocket\Mail\System($config['mail']));
$error = Rocket::set('error', new \Rocket\Error\System($config['error']));
$translator = Rocket::set('translator', new \Rocket\Translator\System($request, $config['translator']));
Rocket::launch($database, $config['rocket']);

$data = array();
try{
	$data['data'] = Rocket::handle($request->uri(), $request->method(), $request->data());
	$data['code'] = 200;
}catch (NotFoundException $e){
	$data['code'] = 404;
	$data['errors'] = "not.found";
}catch (InvalidInputDataException $e){
	$data['code'] = 400;
	$data['errors'] = $e->errors();
}catch (UnauthorizedException $e){
	$data['code'] = 401;
	$data['errors'] = $data;
}catch (PDOException $e){
	$data['code'] = 500;
	$data['errors'] = "database.error";
}catch (Exception $e){
	if (true){ // debugging
		// let the error system handle it
		throw $e;
	}else{
		$data['code'] = 500;
		$data['errors'] = "Whoops... Something ugly happened";
	}
}

if (isset($data['errors'])){
	foreach ($data['errors'] as $key => $value){
		$data['errors'][$key] = $translator->translate($value);
	}
}

// -------

$response->status($data['code']);
$response->header('Content-Type', 'application/json');
$response->body(json_encode($data));
$response->send();
