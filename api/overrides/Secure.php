<?php

class Secure{

	static function check(){
		return false;
	}

	static function on_input(&$data){
		if (1){
			$data = array('error' => 'unauthorized');
			throw new \UnauthorizedException($data);
		}
	}
}