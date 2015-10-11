<?php

/**
 * Template Renderer
 *
 * Template System for RocketPHP Framework
 *
 * @author  Juan Camilo Estela <juancamiloestela@revolutiondynamics.co>
 * @version  0.1
 */
namespace Rocket\Template;

class Renderer{

	protected $renderers;
	private $engine;

	function __construct($renderers, $engine){
		$this->renderers = $renderers;
		$this->engine = $engine;
	}

	function render($token){
		if (array_key_exists($token['type'], $this->renderers)){
			return $this->renderers[$token['type']]($token);
		}
		return '['.$token['type'].']';
		//die('renderer for '.$token['type'].' is not defined');
	}
}