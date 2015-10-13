<?php
namespace Rocket\Site;

class Page{
	
	protected $template;

	function __construct($template){
		$this->template = $template;
	}
}