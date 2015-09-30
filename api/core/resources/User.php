<?php 
class User {

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
		if (strlen($value) < 3){ $errors[] = "name.tooShort"; }
		return $errors;
	}

	function receive_email($value, &$errors) {
		$errors = array_merge($errors, $this->validate_email($value));
		return $value;
	}

	function validate_email($value) {
		$errors = array();
		if (!filter_var($value, FILTER_VALIDATE_EMAIL)){ $errors[] = "email.incorrectType.email"; }
		if (strlen($value) > 30){ $errors[] = "email.tooLong"; }
		if (strlen($value) < 5){ $errors[] = "email.tooShort"; }
		return $errors;
	}

	function receive_password($value, &$errors) {
		$errors = array_merge($errors, $this->validate_password($value));
		return $value;
	}

	function validate_password($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "password.incorrectType.string"; }
		if (strlen($value) > 60){ $errors[] = "password.tooLong"; }
		if (strlen($value) < 60){ $errors[] = "password.tooShort"; }
		return $errors;
	}

	function GET_users_when_logged($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		Rocket::call(array("paginated", "on_input"), $data);
		$query = "SELECT id,name,email FROM User";
		Rocket::call(array("paginated", "on_query"), $query, $data);
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		Rocket::call(array("paginated", "on_data"), $data);
		return $data;
	}

	function GET_users_id_when_logged($data, $id) {
		$errors = array();

		$data["id"] = $id;

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "SELECT id,name FROM User WHERE id = :id LIMIT 1";
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

}