<?php 
class Blogs {

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

	function receive_description($value, &$errors) {
		$errors = array_merge($errors, $this->validate_description($value));
		return $value;
	}

	function validate_description($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "description.incorrectType.string"; }
		if (strlen($value) > 400){ $errors[] = "description.tooLong"; }
		return $errors;
	}

	function receive_created($value, &$errors) {
		$errors = array_merge($errors, $this->validate_created($value));
		return $value;
	}

	function validate_created($value) {
		$errors = array();
		if (!is_date($value, 'Y-m-d H:i:s')){ $errors[] = "created.incorrectType.datetime"; }
		return $errors;
	}

	function receive_updated($value, &$errors) {
		$errors = array_merge($errors, $this->validate_updated($value));
		return $value;
	}

	function validate_updated($value) {
		$errors = array();
		if (!is_date($value, 'Y-m-d H:i:s')){ $errors[] = "updated.incorrectType.datetime"; }
		return $errors;
	}

	function GET_blogs_when_public($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		Rocket::call(array("paginated", "on_input"), $data);
		$query = "SELECT * FROM Blogs";
		Rocket::call(array("paginated", "on_query"), $query, $data);
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		Rocket::call(array("paginated", "on_data"), $data);
		return $data;
	}

	function POST_blogs_when_public($data) {
		$errors = array();

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		Rocket::call(array("TimeTracked", "on_input"), $data);
		$fields = array();
		$values = array();
		$columns = array("name","description","created","updated");
		foreach ($columns as $column) {
			if (isset($data[$column])){
				$fields[] = $column;
				$values[] = ':'.$column;
			}
		}
		$query = "INSERT INTO Blogs (".implode(',', $fields).") VALUES (".implode(',', $values).")";
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$id = $this->db->lastInsertId();
		if (!$id){
			throw new Exception('Could not create resource');
		}
		$query = "SELECT * FROM Blogs WHERE id = :id LIMIT 1";
		$statement = $this->db->prepare($query);
		$statement->execute(array("id" => $id));
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if (!$data){
			throw new Exception('Could not create resource');
		}
		return $data;
	}

	function GET_blogs_id_when_public($data, $id) {
		$errors = array();

		$data["id"] = $id;

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		$query = "SELECT * FROM Blogs WHERE id = :id LIMIT 1";
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if (!$data){
			throw new NotFoundException();
		}
		return $data;
	}

	function PUT_blogs_id_when_public($data, $id) {
		$errors = array();

		$data["id"] = $id;

		if (count($errors) > 0) {
			throw new InvalidInputDataException($errors);
		}
		Rocket::call(array("TimeTracked", "on_input"), $data);
//PUTTING
		return $data;
	}

}