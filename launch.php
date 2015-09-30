<?php
/**
 * TODO
 * - detect lang on request class, accept headers and override lang param
 * * execute all contexts one time and keep state
 * - cleanup and reorder rocket.php code
 * - debugging global to control if exceptions are propagated or not
 * - build hooks/delegates logic
 * - generator script
 * - sync db
 * - cleanup config/folder structure/composer project
 * - datatypes: date, numbers, float, int, relations
 * - traits paged, etc
 * - exposed
 * - documentator script
 *
 * Roadmap
 * - yaml
 * - handle caching
 * - handle throttling
 * - CSRF
 */


define('DEVELOPING', true);

if (!defined('APP_PATH')){
	define('APP_PATH', __DIR__);
}

if (!defined('APP_ROOT')){
	//define('APP_ROOT', str_replace($_SERVER['DOCUMENT_ROOT'], '', getcwd()));
}

define('PUBLIC_PATH', getcwd());
chdir(APP_PATH);

/*
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
define('CURRENT_URL', str_replace(APP_ROOT, $uri, APP_URL));*/


include 'rocket/rocket/src/Rocket.php';
include 'rocket/database/src/System.php';
include 'rocket/mail/src/System.php';
include 'rocket/error/src/System.php';
include 'rocket/translator/src/System.php';
include 'rocket/api/src/System.php';

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


class Delegates{

	static function receiveName(&$value, &$errors){
		echo __METHOD__ . PHP_EOL;
		echo "value: " . PHP_EOL; print_r($value); echo PHP_EOL;
		echo "errors: " . PHP_EOL; print_r($errors);
		echo PHP_EOL . PHP_EOL;
		$value = $value . '_name';
		return $value;
	}

	static function publicPatientsError(&$data, &$errors, $mail){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo "errors: " . PHP_EOL; print_r($errors);
		echo "mail: " . PHP_EOL; print_r($mail);
		echo PHP_EOL . PHP_EOL;
		$errors[] = 'injected error';
		return true; // enables/disables exception
	}
	static function publicPatientsInput(&$data, $mail){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo "mail: " . PHP_EOL; print_r($mail);
		echo PHP_EOL . PHP_EOL;
	}
	static function publicPatientsQuery($query, $data){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo "query: " . PHP_EOL; print_r($query);
		echo PHP_EOL . PHP_EOL;
	}
	static function publicPatientsData($data, $mail){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo PHP_EOL . PHP_EOL;
		print_r($mail);
	}
}

class Contexts{

	static function is_logged($mail){
		return true;
	}

	static function is_owner(){
		return false;
	}

	static function is_admin(){
		return false;
	}
}


class Paginated{

	static function on_input(&$data, $mail){
		// ensure values are set
		$data['offset'] = isset($data['offset']) ? $data['offset'] : 0;
		$data['length'] = isset($data['length']) ? $data['length'] : 10;
	}

	static function on_query(&$query, $data){
		// modify query
		$query = str_replace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $query) . " LIMIT :offset, :length";
	}

	static function on_data($data, $database, $response){
		// get total records and push them to response metadata
		$statement = $database->prepare("SELECT FOUND_ROWS();");
		$statement->execute();
		$total = $statement->fetch();
		$response->setMetadata('total', $total[0]);
	}
}

class Secure{
	static function on_input(&$data){
		if (true){
			//$data['authorized'] = false;
			//throw new \UnauthorizedException();
		}
	}
}

$request = Rocket::set('request', new \Rocket\Request($config['request']));
$response = Rocket::set('response', new \Rocket\Response($config['response']));
$database = Rocket::set('database', new \Rocket\Database\System($config['database']));
$mail = Rocket::set('mail', new \Rocket\Mail\System($config['mail']));
$error = Rocket::set('error', new \Rocket\Error\System($config['error']));
$translator = Rocket::set('translator', new \Rocket\Translator\System($request, $config['translator']));
$api = Rocket::set('api', new \Rocket\Api\System($database, $config['api']));


$api->launch();//$database, $config['rocket']);



$data = array();
try{
	$data['data'] = $api->handle($request->uri(), $request->method(), $request->data());
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
	print_r($e->getMessage());
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
	if (is_array($data['errors'])){
		foreach ($data['errors'] as $key => $value){
			$data['errors'][$key] = $translator->translate($value);
		}
	}else{
		$data['errors'] = $translator->translate($data['errors']);
	}
}

// -------

$response->status($data['code']);
$data = array_merge($data, $response->getMetadata());
$response->header('Content-Type', 'application/json');
$response->body(json_encode($data));
$response->send();
