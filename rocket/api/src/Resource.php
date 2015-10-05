<?php
namespace Rocket\Api;

class Resource{

	protected $db;
	protected $fields = array();
	protected static $notExposed = array();

	function __construct($db){
		$this->db = $db;
	}

	protected function getDataForQuery($query, $data){
		$queryData = array();
		preg_match_all('/:([a-zA-Z0-9_]+)/im', $query, $matches, PREG_SET_ORDER);
		if (count($matches)){
			foreach ($matches as $match){
				if (isset($data[$match[1]])){
					$queryData[$match[1]] = $data[$match[1]];
				}
			}
		}
		return $queryData;
	}

	public static function methodIsExposed($method){
		if (in_array($method, static::$notExposed)){
			return false;
		}
		return true;
	}
}