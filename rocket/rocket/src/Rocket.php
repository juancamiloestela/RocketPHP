<?php

include 'Exceptions.php';
include 'ArrayObject.php';
include 'Request.php';
include 'Response.php';

class Rocket{

	private static $instances = [];

	public static $config = array();
	protected static $defaults = array(

	);


	public static function &set($id, $instance){
		static::$instances[$id] = $instance;
		return $instance;
	}

	public static function get($id){
		if (isset(static::$instances[$id])){
			return static::$instances[$id];
		}
		return null;
	}

	public static function callArray($callable, $args = array()){
		if (is_array($callable)){
			if (!class_exists($callable[0])){
				if (isset(static::$instances[$callable[0]])){
					$callable[0] = static::$instances[$callable[0]];
				}
			}
			$r = new \ReflectionMethod($callable[0], $callable[1]);
		}else if (is_string($callable)){
			$f = new \ReflectionFunction($callable);
		}

		$params = $r->getParameters();
		$offset = count($args);
		for ($i = $offset; $i < count($params); $i++){
			$arg = static::get($params[$i]->getName());
			if ($arg === null){
				if ($params[$i]->isOptional()) {
					$arg = $params[$i]->getDefaultValue();
				}else{
					if (is_object($callable[0])){
						$fn = get_class($callable[0]);
					}else{
						$fn = $callable[0];
					}
					if (isset($callable[1])){
						$fn .= '::'.$callable[1];
					}
					throw new \Exception('Could not find value or instance to inject for argument "$'.$params[$i]->getName().'" in function "'.$fn.'"');
				}
			}
			$args[] = $arg;
		}

		return call_user_func_array($callable, $args);
	}

	public static function call($callable, &$p0 = null, &$p1 = null, &$p2 = null, &$p3 = null, &$p4 = null, &$p5 = null, &$p6 = null){
		// ugly hack to make variable number of args by reference
		$args = array();
		for ($i = 0; $i < 7; $i++){
			$name = 'p'.$i;
			if ($$name !== null){
				$args[] = &$$name;
			}
		}

		return static::callArray($callable, $args);




		// ugly hack to make variable number of args by reference
		$args = array();
		for ($i = 0; $i < 7; $i++){
			$name = 'p'.$i;
			if ($$name !== null){
				$args[] = &$$name;
			}
		}

		//$args = func_get_args();
		//$callable = array_shift($args);

		if (is_array($callable)){
			if (!class_exists($callable[0])){
				if (isset(static::$instances[$callable[0]])){
					$callable[0] = static::$instances[$callable[0]];
				}
			}
			$r = new \ReflectionMethod($callable[0], $callable[1]);
		}else if (is_string($callable)){
			$f = new \ReflectionFunction($callable);
		}

		$params = $r->getParameters();
		$offset = count($args);
		for ($i = $offset; $i < count($params); $i++){
			$arg = static::get($params[$i]->getName());
			if ($arg === null){
				if ($params[$i]->isOptional()) {
					$arg = $params[$i]->getDefaultValue();
				}else{
					throw new \Exception('Could not find value or instance to inject at $'.$params[$i]->getName());
				}
			}
			$args[] = $arg;
		}

		return call_user_func_array($callable, $args);
	}
}