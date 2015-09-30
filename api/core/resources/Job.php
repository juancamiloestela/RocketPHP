<?php 
class Job {

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

	function receive_role($value, &$errors) {
		$errors = array_merge($errors, $this->validate_role($value));
		return $value;
	}

	function validate_role($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "role.incorrectType.string"; }
		if (strlen($value) > 30){ $errors[] = "role.tooLong"; }
		if (strlen($value) < 1){ $errors[] = "role.tooShort"; }
		return $errors;
	}

	function receive_employees($value, &$errors) {
		$errors = array_merge($errors, $this->validate_employees($value));
		return $value;
	}

	function validate_employees($value) {
		$errors = array();
		return $errors;
	}

	function employees($id) {
		// TODO: return query here so that users can customize result eg. LIMIT, ORDER BY, WHERE x, etc
		$query = "SELECT Patient.* FROM Patient JOIN employees_jobs ON Patient.id = employees_jobs.employees_id WHERE employees_jobs.jobs_id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
	}

	function GET_cars_when_public($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "select * from Job";
		$queryData = $this->getDataForQuery($query, $data); // array();
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

}