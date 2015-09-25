<?php

class Contexts{

	function __construct(){
		
	}

	static function is_logged(){
		return true;
	}

	static function owns(){
		return false;
	}

	static function hook($input, $data, $translator){
		$data['hooked'] = $translator::translate("name.required");
		$data['input'] = $input;
		return $data;
	}
}

function is_admin(){
	return true;
}