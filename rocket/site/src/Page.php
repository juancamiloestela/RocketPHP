<?php
namespace Rocket\Site;

class Page{
	
	protected $system;
	protected $template;
	protected $errors = array();

	function __construct($system, $template){
		$this->system = $system;
		$this->template = $template;
	}
}