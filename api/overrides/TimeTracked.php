<?php

class TimeTracked{

	static function on_properties(&$properties){
		$properties->created = (object)array("type" => "datetime");
		$properties->updated = (object)array("type" => "datetime");
	}

	static function on_input(&$data, $request){
		$datetime = (new \DateTime('now'))->format('Y-m-d H:i:s');
		if ($request->method() == 'POST'){
			$data['created'] = $datetime;
		}else{
			$data['updated'] = $datetime;
		}
	}
}