<?php

class Paginated{

	static function on_spec(&$spec){
		$spec->properties = array(
			"created" => array("type" => "datetime"),
			"updated" => array("type" => "datetime")
		);
	}

	static function on_input(&$data, $mail){
		// ensure values are set
		$data->offset = isset($data->offset) ? $data->offset : 0;
		$data->length = isset($data->length) ? $data->length : 10;
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