<?php

class NotFoundException extends Exception{

}

class InvalidInputDataException extends Exception{
	protected $errors = array();

	function __construct($errors){
		$this->errors = $errors;
	}

	function errors(){
		// TODO: translate keys to user friendly message
		return $this->errors;
	}
}

class UnauthorizedException extends Exception{

}