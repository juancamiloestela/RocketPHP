<?php 
class Patient {

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

	function receive_name($value, &$errors) {
		$errors = array_merge($errors, $this->validate_name($value));
		\Rocket::call(array("Delegates", "receiveName"), $value, $errors);
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
		return $value;
	}

	function validate_age($value) {
		$errors = array();
		if (!is_int($value)){ $errors[] = "age.incorrectType.int"; }
		if ($value > 99){ $errors[] = "age.tooLarge"; }
		if ($value < 0){ $errors[] = "age.tooSmall"; }
		return $errors;
	}

	function receive_motorcycle($value, &$errors) {
		$errors = array_merge($errors, $this->validate_motorcycle($value));
		return $value;
	}

	function validate_motorcycle($value) {
		$errors = array();
		return $errors;
	}

	function motorcycle($id) {
		// TODO: return query here so that users can customize result eg. LIMIT, ORDER BY, WHERE x, etc
		$query = "SELECT * FROM Motorcycle WHERE id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
	}

	function receive_cars($value, &$errors) {
		$errors = array_merge($errors, $this->validate_cars($value));
		return $value;
	}

	function validate_cars($value) {
		$errors = array();
		if (count($value) > 3){ $errors[] = "cars.tooMany"; }
		return $errors;
	}

	function cars($id) {
		// TODO: return query here so that users can customize result eg. LIMIT, ORDER BY, WHERE x, etc
		$query = "SELECT * FROM Car WHERE cars_id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
	}

	function receive_jobs($value, &$errors) {
		$errors = array_merge($errors, $this->validate_jobs($value));
		return $value;
	}

	function validate_jobs($value) {
		$errors = array();
		return $errors;
	}

	function jobs($id) {
		// TODO: return query here so that users can customize result eg. LIMIT, ORDER BY, WHERE x, etc
		$query = "SELECT Job.* FROM Job JOIN employees_jobs ON Job.id = employees_jobs.jobs_id WHERE employees_jobs.employees_id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
	}

	function GET_patients_when_public($data) {
		$errors = array();

		// check for required input data
		if (!isset($data["name"])){ $errors[] = "name.required"; }
		else{ $data["name"] = $this->receive_name($data["name"], $errors); }

		if (count($errors) > 0) {
			if (Rocket::call(array("Delegates", "publicPatientsError"), $data, $errors)){
				throw new InvalidInputDataException($errors);
			}
		}
		Rocket::call(array("Delegates", "publicPatientsInput"), $data);
		$query = "select * from Patient";
		$queryData = $this->getDataForQuery($query, $data); // array();
		Rocket::call(array("Delegates", "publicPatientsQuery"), $data, $queryData, $query);
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		Rocket::call(array("Delegates", "publicPatientsData"), $data);
		return $data;
	}

	function GET_patients_when_logged($data) {
		$errors = array();

		// check for required input data
		if (!isset($data["name"])){ $errors[] = "name.required"; }
		else{ $data["name"] = $this->receive_name($data["name"], $errors); }
		if (!isset($data["email"])){ $errors[] = "email.required"; }
		else{ $data["email"] = $this->receive_email($data["email"], $errors); }
		if (!isset($data["phone"])){ $errors[] = "phone.required"; }
		else{ $data["phone"] = $this->receive_phone($data["phone"], $errors); }

		// check optional input data if present
		if (isset($data["age"])){ $data["age"] = $this->receive_age($data["age"], $errors); }

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "select id,name,email,phone from Patient where id = :id LIMIT 1";
		$queryData = $this->getDataForQuery($query, $data); //array('id' => "1");
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

	function GET_patients_when_owns($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "select * from Patient";
		$queryData = $this->getDataForQuery($query, $data); // array();
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		return $data;
	}

	function GET_patients_id_path_code_when_owns($data, $id, $code) {
		$errors = array();

		$data["id"] = $id;
		$data["code"] = $code;

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "";
		$queryData = $this->getDataForQuery($query, $data); //array('id' => "1");
		$statement = $this->db->prepare($query);
		$statement->execute($queryData);
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		return $data;
	}

}