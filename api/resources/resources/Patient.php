<?php 
class Patient {

	protected $db;
	function __construct($db){
		$this->db = $db;
	}
	function receive_name($value, &$errors) {
		$errors = array_merge($errors, $this->validate_name($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_name($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "name.incorrectType.string"; }
		if (strlen($value) > 30){ $errors[] = "name.tooLong"; }
		if (strlen($value) < 1){ $errors[] = "name.tooShort"; }
		return $errors;
	}

	function receive_email($value, &$errors) {
		$errors = array_merge($errors, $this->validate_email($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_email($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "email.incorrectType.string"; }
		if (strlen($value) > 30){ $errors[] = "email.tooLong"; }
		if (strlen($value) < 1){ $errors[] = "email.tooShort"; }
		if (!preg_match("/[a-z]+@[a-z]+/", $value)){ $errors[] = "email.patternMatch"; }
		return $errors;
	}

	function receive_password($value, &$errors) {
		$errors = array_merge($errors, $this->validate_password($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_password($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "password.incorrectType.string"; }
		if (strlen($value) > 60){ $errors[] = "password.tooLong"; }
		if (strlen($value) < 60){ $errors[] = "password.tooShort"; }
		return $errors;
	}

	function receive_phone($value, &$errors) {
		$errors = array_merge($errors, $this->validate_phone($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_phone($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "phone.incorrectType.string"; }
		if (strlen($value) > 10){ $errors[] = "phone.tooLong"; }
		if (strlen($value) < 10){ $errors[] = "phone.tooShort"; }
		if (!preg_match("/[0-9]+/", $value)){ $errors[] = "phone.patternMatch"; }
		return $errors;
	}

	function receive_age($value, &$errors) {
		$errors = array_merge($errors, $this->validate_age($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_age($value) {
		$errors = array();
		if (!is_numeric($value)){ $errors[] = "age.incorrectType.numeric"; }
		return $errors;
	}

	function receive_motorcycle($value, &$errors) {
		$errors = array_merge($errors, $this->validate_motorcycle($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_motorcycle($value) {
		$errors = array();
		return $errors;
	}

	function receive_cars($value, &$errors) {
		$errors = array_merge($errors, $this->validate_cars($value));
		// TODO: $value = customHook($value);
		return $value;
	}

	function validate_cars($value) {
		$errors = array();
		return $errors;
	}

	function GET_patients_when_public() {
		$data = array();

		$errors = array();

		// check query string data
		$page = $this->receive_page($_GET["page"], $errors);

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
			return $errors;
		} else {
			// TODO: $data = customHook($data);
			$statement = $this->db->prepare("select * from some_table;");
			$statement->execute();
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		return $data;
	}

	function GET_patients_when_logged() {
		$data = array();

		$errors = array();
$input = array();
		// check for required input data
		if (!isset($_GET["name"])){ $errors[] = "name.required"; }
		else{ $input['name'] = $this->receive_name($_GET["name"], $errors); }
		if (!isset($_GET["email"])){ $errors[] = "email.required"; }
		else{ $input['email'] = $this->receive_email($_GET["email"], $errors); }
		if (!isset($_GET["phone"])){ $errors[] = "phone.required"; }
		else{ $input['phone'] = $this->receive_phone($_GET["phone"], $errors); }

		// check optional input data if present
		if (isset($age)){ $input['age'] = $this->receive_age($_REQUEST["age"], $errors); }

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
			return $errors;
		} else {
			// TODO: $data = customHook($data);
			$statement = $this->db->prepare("select id,name,email,phone from some_table where id = :id LIMIT 1;");
			$statement->execute(array('id' => "1"));
			$data = $statement->fetch(PDO::FETCH_ASSOC);

$data = Rocket::call(array("Contexts", "hook"), $input, $data);
		}
		return $data;
	}

	function GET_patients_when_owns() {
		$data = array();

		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
			return $errors;
		} else {
			// TODO: $data = customHook($data);
			$statement = $this->db->prepare("select * from some_table;");
			$statement->execute();
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		return $data;
	}

	function GET_patients_id_path_code_when_owns($id, $code) {
		$data = array();

		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
			return $errors;
		} else {
			// TODO: $data = customHook($data);
			$statement = $this->db->prepare("select name,email from some_table where id = :id LIMIT 1;");
			$statement->execute(array('id' => "1"));
			$data = $statement->fetch(PDO::FETCH_ASSOC);
		}
		return $data;
	}

}