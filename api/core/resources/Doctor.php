<?php 
class Doctor {

	protected $db;
	function __construct($db){
		$this->db = $db;
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

	function GET_doctor_when_public() {
		$data = array();
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		// TODO: $data = customHook($data);
		$query = "select * from Doctor";
		$queryData = array();
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

}