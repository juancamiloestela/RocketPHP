<?php

include 'Exceptions.php';
include 'Request.php';
include 'Response.php';

class Rocket{

	private static $instances = [];
	private static $classes = [];

	protected static $routes = array();
	protected static $contexts = array();
	protected static $db;

	public static $config = array();
	protected static $defaults = array(
		'context_index_file' => 'api/resources/contexts.php',
		'routes_file' => 'api/resources/routes.php',
		'contexts_file' => 'api/contexts/contexts.php',
		'delegates_file' => 'api/delegates/delegates.php',
		'resources_path' => 'api/resources/'
	);

	public static function launch($db, $config = array()){
		static::$config = array_merge(static::$defaults, $config);

		static::$contexts = include static::$config['context_index_file'];
		static::$routes = include static::$config['routes_file'];

		include static::$config['contexts_file'];
		include static::$config['delegates_file'];

		static::$db = $db;

		static::evaluateContexts();
		// static::$db = new PDO("mysql:host=127.0.0.1;dbname=generator", "jcestela", "tortuga");
		// static::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public static function &set($id, $instance){
		static::$instances[$id] = $instance;
		static::$classes[get_class($instance)] = &static::$instances[$id];
		return $instance;
	}

	public static function get($id){
		if (isset(static::$instances[$id])){
			return static::$instances[$id];
		}
		throw new \Exception('Could not find instance of '.$id);
	}

	public static function call(){
		$args = func_get_args();
		$callable = array_shift($args);

		if (is_array($callable)){
			$r = new \ReflectionMethod($callable[0], $callable[1]);
		}else if (is_string($callable)){
			$f = new \ReflectionFunction($callable);
		}

		$params = $r->getParameters();
		$offset = count($args);
		for ($i = $offset; $i < count($params); $i++){
			$args[] = static::get($params[$i]->getName());
		}

		return call_user_func_array($callable, $args);
	}

	private static function evaluateContexts()
	{
		foreach (static::$contexts as $contextName => $context){
			//echo "Checking Context " . $contextName . PHP_EOL;
			foreach ($context as $i => $check){
				//echo "\tTesting " . $check . PHP_EOL;
				if (!$check[0]){
					$check = $check[1];
				}

				if (!is_callable($check)){
					if (is_array($check)){
						throw new Exception('Context check static method "' . $check[0] . '::' . $check[1] . '()" does not exist');
					}else{
						throw new Exception('Context check function "' . $check . '()" does not exist');
					}
				}

				static::$contexts[$contextName][$i][2] = call_user_func_array($check, array());
			}
		}
	}

	public static function getCurrentContext()
	{
		foreach (static::$contexts as $contextName => $context){
			foreach ($context as $i => $check){
				if ( !static::$contexts[$contextName][$i][2] ){
					continue 2;
				}
			}
			return $contextName;
		}
		return false;
	}

	public static function handle($uri, $request_method, $data = array()){
		/*$uri = $_SERVER['REQUEST_URI'];
		$REQUEST_METHOD = 'GET';

		$uri = str_replace(APP_ROOT, '', $uri);
		$uri = explode('?', $uri)[0];*/

		$uri = '/' . $uri;

		foreach (static::$routes as $route => $controller){
			if (preg_match('/^'.$route.'$/', $uri, $matches)){
				$args = array();
				foreach ($controller['args'] as $arg){
					$args[] = isset($matches[$arg]) ? $matches[$arg] : null;
				}

				$context = static::getCurrentContext();

				include static::$config['resources_path'] . $controller['class'] . '.php';
				$instance = new $controller['class'](static::$db);
				$method = $request_method . $controller['method'] . '_when_' . $context; // + context & method signature
//echo $method;
				if (method_exists($instance, $method)){
					return call_user_func_array(array($instance, $method), $args);
				}
				break;
			}
		}
		throw new NotFoundException();
	}
}