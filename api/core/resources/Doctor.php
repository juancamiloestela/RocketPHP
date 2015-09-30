<?php 
class Doctor {

	protected $db;
	function __construct($db){
		$this->db = $db;
	}

	protected function getDataForQuery($query, $data){
		$queryData = array();
		preg_match_all('/:([a-zA-Z0-9_]+)/im', $query, $matches, PREG_SET_ORDER);
		if (count($matches)){
			foreach ($matches as $match){
				$queryData[$match[1]] = $data[$match[1]];
			}
		}
		return $queryData;
	}

	function receive_name($value, &$errors) {
		$errors = array_merge($errors, $this->validate_name($value));
		return $value;
	}

	function validate_name($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "name.incorrectType.string"; }
		if (strlen($value) > 30){ $errors[] = "name.tooLong"; }
		if (strlen($value) < 1){ $errors[] = "name.tooShort"; }
		return $errors;
	}

	function GET_doctor_when_public($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "SELECT * FROM Doctor";
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

}