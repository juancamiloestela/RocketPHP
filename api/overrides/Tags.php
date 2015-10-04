<?php

class Tags{
	static function on_query(&$query, $data){
		// modify query
		$query = str_replace('id = :id', 'text = :tag', $query);
	}
}