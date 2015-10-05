<?php

class ResponseTime{
	protected static $startTime;

	public static function on_start($data){
		static::$startTime = microtime(true);
	}

	public static function on_data($data, $response){
		$delta = round((microtime(true) - static::$startTime) * 1000, 4) . " ms";
		$response->setMetadata('response-time', $delta);
	}
}