<?php
namespace Rocket\Site;

include 'Page.php';

class System
{

	public $template;
	public $response;
	protected $instance;
	public $config = array();
	protected $defaults = array(
		'payload' => 'site/',
		'spec_file' => 'spec/spec.json',
		'core_path' => 'core/'
	);

	protected $generator;

	protected $routes = array();
	protected $contexts = array();
	protected $checks = array();

	public function __construct($template, $response, $config = array())
	{
		$this->config = array_merge($this->defaults, $config);
		$this->template = $template;
		$this->response = $response;

		$this->checkFolderStructure();
		spl_autoload_register(array($this, 'autoload'));

		if (DEVELOPING){
			include 'Generator.php';
			$this->generator = new Generator($this);
		}

		$this->contexts = include $this->config['core_path'] . 'contexts.php';
		$this->routes = include $this->config['core_path'] . 'routes.php';

		$this->evaluateContexts();
	}

	public function checkFolderStructure()
	{

		$this->config['core_path'] = $this->config['payload'] . $this->config['core_path'];

		// TODO: this should happen to all systems, extend?
		if (!file_exists($this->config['payload'])){
			mkdir($this->config['payload'], 0755, true);
		}

		if (!file_exists($this->config['core_path'])){
			mkdir($this->config['core_path'], 0755, true);
			file_put_contents($this->config['core_path'] . '!DO_NOT_EDIT_FILES_IN_HERE.txt', 'The contents of this folder have been autogenerated by RocketPHP, changes you make to these files will be lost!');
		}
	}

	public function autoload($class)
	{
		$file = str_replace('\\', DIRECTORY_SEPARATOR, $class);

		$filename = $this->config['payload'] . 'overrides' . DIRECTORY_SEPARATOR . $file . '.php';
		if (file_exists($filename)){
			include $filename;
			return;
		}

		if (stripos($file, 'pages') !== false){
			$filename = $this->config['core_path'] . $file . '.php';
			if (file_exists($filename)){
				include $filename;
				return;
			}
		}
	}

	private function evaluateContexts()
	{
		$this->checks = array();

		foreach ($this->contexts as $contextName => $context){
			foreach ($context as $i => $check){
				if (!$check[0]){
					$check = $check[1];
				}

				if (!isset($this->checks[ $check[0].'.'.$check[1] ])){
					if (!is_callable($check)){
						if (is_array($check)){
							throw new \Exception('Context check static method "' . $check[0] . '::' . $check[1] . '()" does not exist');
						}else{
							throw new \Exception('Context check function "' . $check . '()" does not exist');
						}
					}

					$this->checks[ $check[0].'.'.$check[1] ] = \Rocket::call($check);
				}
			}
		}
	}

	public function getCurrentContext()
	{
		foreach ($this->contexts as $contextName => $context){
			foreach ($context as $i => $check){
				if (!$check[0]){
					$check = $check[1];
				}

				if ( !$this->checks[ $check[0].'.'.$check[1] ] ){
					continue 2;
				}
			}
			return $contextName;
		}
		return false;
	}

	public function launch($uri, $request_method, $data = array())
	{

		foreach ($this->routes as $route => $controller){
//echo $uri . ' ' . $route . PHP_EOL;
			if (preg_match('/^'.$route.'$/', $uri, $matches)){
				$args = array($data);
				foreach ($controller['args'] as $arg){
					$args[] = isset($matches[$arg]) ? $matches[$arg] : null;
				}

				$context = $this->getCurrentContext();

				$controller['class'] = 'Pages\\'.$controller['class'];
				if (get_class($this->instance) != $controller['class']){
					$this->instance = new $controller['class']($this, $this->template);
				}
				$method = $request_method . '_when_' . $context;

				if (method_exists($this->instance, $method)){
					try{
						$body = call_user_func_array(array($this->instance, $method), $args);
						$status = 200;
						$this->response->body($body);
					}catch (\NotFoundException $e){
						die('redirect to: not found');
					}catch (\InvalidInputDataException $e){
						die('invalid input: do what?');
					}catch (\UnauthorizedException $e){
						die('redirect to: login');
					}catch (\PDOException $e){
						die('redirect to: error 500');
						print_r($e->getMessage());
					}catch (\Exception $e){
						if (true){ // debugging
							// let the error system handle it
							throw $e;
						}else{
							die('redirect to: error 500');
						}
					}

					$this->response->status($status);
					$this->response->send();
				}
				return;
			}
		}
		throw new \NotFoundException();
	}

}

