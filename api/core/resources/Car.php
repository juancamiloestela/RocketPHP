<?php 
class Car {

	protected $db;
	function __construct($db){
		$this->db = $db;
	}

	function getDataForQuery($query, $data){
		$queryData = array();
		preg_match_all('/:([a-zA-Z0-9_]+)/im', $query, $matches, PREG_SET_ORDER);
		if (count($matches)){
			foreach ($matches as $match){
				$queryData[$match[1]] = $data[$match[1]];
			}
		}
		echo 'QUERY DATA '.$query;print_r($queryData);
		return $queryData;
	}

	function receive_brand($value, &$errors) {
		$errors = array_merge($errors, $this->validate_brand($value));
		return $value;
	}

	function validate_brand($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "brand.incorrectType.string"; }
		if (strlen($value) > 30){ $errors[] = "brand.tooLong"; }
		if (strlen($value) < 1){ $errors[] = "brand.tooShort"; }
		return $errors;
	}

	function receive_owner($value, &$errors) {
		$errors = array_merge($errors, $this->validate_owner($value));
		return $value;
	}

	function validate_owner($value) {
		$errors = array();
		return $errors;
	}

	function owner($id) {
		// TODO: return query here so that users can customize result eg. LIMIT, ORDER BY, WHERE x, etc
		$query = "SELECT * FROM Patient WHERE id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
	}

	function GET_cars_when_public($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "select * from Car";
		$queryData = $this->getDataForQuery($query, $data); // array();
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

}