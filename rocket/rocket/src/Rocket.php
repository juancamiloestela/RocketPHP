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
		throw new \Exception('Could not find instance of '.$id);
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

		//$args = func_get_args();
		//$callable = array_shift($args);

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
}