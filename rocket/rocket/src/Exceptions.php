<?php

class NotFoundException extends Exception{
	
	private $data;

	function __construct($message = "", $code = 0, $previous = NULL, $data = array())
	{
		$this->data = $data;
		parent::__construct($message, $code, $previous);
	}

	function data()
	{
		return $this->data;
	}
}

class InvalidInputDataException extends Exception{
	protected $errors = array();

	function __construct($errors)
	{
		// TODO: make this function standard, ie. $code, $previous, etc
		$this->errors = $errors;
	}

	function errors()
	{
		// TODO: translate keys to user friendly message
		return $this->errors;
	}
}

class UnauthorizedException extends Exception{
	private $data;

	function __construct($data)
	{
		// TODO: make this function standard, ie. $code, $previous, etc
		$this->data = $data;
	}

	function data()
	{
		return $this->data;
	}
}