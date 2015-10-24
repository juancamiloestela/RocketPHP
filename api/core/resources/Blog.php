<?php 
/**
 * This class has been autogenerated by RocketPHP
 */

namespace Resources;

class Blog extends \Rocket\Api\Resource{

	protected $fields = array("name","description","posts","owner","created","updated");
	protected static $notExposed = array("");

	function receive_name($value, &$errors) {
		$errors = array_merge($errors, $this->validate_name($value));
		return $value;
	}

	function validate_name($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "Blog.name.incorrectType.string"; }
		if (strlen($value) > 30){ $errors[] = "Blog.name.tooLong"; }
		if (strlen($value) < 3){ $errors[] = "Blog.name.tooShort"; }
		return $errors;
	}

	function receive_description($value, &$errors) {
		$errors = array_merge($errors, $this->validate_description($value));
		return $value;
	}

	function validate_description($value) {
		$errors = array();
		if (!is_string($value)){ $errors[] = "Blog.description.incorrectType.string"; }
		if (strlen($value) > 400){ $errors[] = "Blog.description.tooLong"; }
		if (strlen($value) < 1){ $errors[] = "Blog.description.tooShort"; }
		return $errors;
	}

	function receive_posts($value, &$errors) {
		$errors = array_merge($errors, $this->validate_posts($value));
		return $value;
	}

	function validate_posts($value) {
		$errors = array();
		return $errors;
	}

	function posts($id) {
		// TODO: return query here so that users can customize result eg. LIMIT, ORDER BY, WHERE x, etc
		$query = "SELECT * FROM Post WHERE blog_id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
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
		$query = "SELECT * FROM User WHERE id = :id";
		$statement = $this->db->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetch(\PDO::FETCH_ASSOC);
		
		// TODO: $data = customHook($data);
		return $data;
	}

	function receive_created($value, &$errors) {
		$errors = array_merge($errors, $this->validate_created($value));
		return $value;
	}

	function validate_created($value) {
		$errors = array();
		if (!is_date($value, 'Y-m-d H:i:s')){ $errors[] = "Blog.created.incorrectType.datetime"; }
		return $errors;
	}

	function receive_updated($value, &$errors) {
		$errors = array_merge($errors, $this->validate_updated($value));
		return $value;
	}

	function validate_updated($value) {
		$errors = array();
		if (!is_date($value, 'Y-m-d H:i:s')){ $errors[] = "Blog.updated.incorrectType.datetime"; }
		return $errors;
	}

	function GET_blogs_when_public($data) {
		$errors = array();

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		\Rocket::call(array("paginated", "on_input"), $data);
		$query = "SELECT * FROM Blog";
		\Rocket::call(array("paginated", "on_query"), $query, $data);
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetchAll(\PDO::FETCH_OBJ);
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		\Rocket::call(array("paginated", "on_data"), $data);
		return $data;
	}

	function POST_blogs_when_public($data) {
		$errors = array();

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		// check for required input data
		if (!isset($data->name)){ $errors[] = "Blog.name.required"; }
		else{ $data->name = $this->receive_name($data->name, $errors); }
		if (!isset($data->description)){ $errors[] = "Blog.description.required"; }
		else{ $data->description = $this->receive_description($data->description, $errors); }

		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		$fields = array_intersect($this->fields, array_keys((array)$data));
		$query = "INSERT INTO Blog (".implode(',', $fields).") VALUES (:".implode(', :', $fields).")";
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$id = $this->db->lastInsertId();
		if (!$id){
			throw new \Exception('Could not create resource');
		}
		$data = array("created" => $id);
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		return $data;
	}

	function GET_blogs_id_when_public($data, $id) {
		$errors = array();

		$data->id = $id;

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		$query = "SELECT * FROM Blog WHERE id = :id LIMIT 1";
		$statement = $this->db->prepare($query);
		$statement->execute( $this->getDataForQuery($query, $data) );
		$data = $statement->fetch(\PDO::FETCH_OBJ);
		if (!$data){
			throw new \NotFoundException();
		}
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		return $data;
	}

	function PUT_blogs_id_when_public($data, $id) {
		$errors = array();

		$data->id = $id;

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		$fields = array_intersect($this->fields, array_keys((array)$data));
		$pairs = array();
		foreach ($fields as $field){
			$pairs[] = $field . " = :" . $field;
		}
		$query = "UPDATE Blog SET ".implode(', ', $pairs)." WHERE id = :id";
		$statement = $this->db->prepare($query);
		$result = $statement->execute( $this->getDataForQuery($query, $data) );
		if ($statement->rowCount() == 0){
			throw new \NotFoundException();
		}
		if (!$result){
			throw new \Exception('Could not update resource');
		}
		$data = array("updated" => $id);
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		return $data;
	}

	function DELETE_blogs_id_when_public($data, $id) {
		$errors = array();

		$data->id = $id;

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		$query = "DELETE FROM Blog WHERE id = :id";
		$statement = $this->db->prepare($query);
		$result = $statement->execute( $this->getDataForQuery($query, $data) );
		if ($statement->rowCount() == 0){
			throw new \NotFoundException();
		}
		if (!$result){
			throw new \Exception('Could not delete resource');
		}
		$data = array("deleted" => $id);
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		return $data;
	}

	function GET_blogs_id_posts_when_public($data, $id) {
		$errors = array();

		$data->id = $id;

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		$data = $this->posts($id);
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		return $data;
	}

	function GET_blogs_id_owner_when_public($data, $id) {
		$errors = array();

		$data->id = $id;

		\Rocket::call(array("ResponseTime", "on_start"), $data);
		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		\Rocket::call(array("TimeTracked", "on_input"), $data);
		$data = $this->owner($id);
		\Rocket::call(array("ResponseTime", "on_data"), $data);
		return $data;
	}

}