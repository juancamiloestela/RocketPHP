<?php

/**
 * Template Lexer
 *
 * Template System for RocketPHP Framework
 *
 * @author  Juan Camilo Estela <juancamiloestela@revolutiondynamics.co>
 * @version  0.1
 */
namespace Rocket\Template;


class Lexer{

	protected $grammar;
	protected $src = '';
	public $tokens = array();
	//protected $n = 0;

	function tokenize($type){
		//echo htmlentities('MATCHING '.$type.':  '.substr($this->src, 0, 10)).'<br>';
		if (preg_match('/^'.$this->grammar[$type].'/s', $this->src, $match)){
			$this->src = substr($this->src, strlen($match[0]));
			//echo $type . '['.strlen($match[0]).']: ' . htmlentities($match[0]) . '<br>';
			return $match[0];
		}
		return false;
	}

	function lex($src, $grammar){
		$this->src = $src;
		$this->grammar = $grammar;
		$this->tokens = array();

		while ($this->src){
			foreach ($this->grammar as $name => $grammar){
				$token = $this->tokenize($name);
				if ($token !== false){
					$this->tokens[] = array(
						'type' => $name,
						'text' => $token
					);
					break;
				}
				//die('Unrecognized input ');
			}

			/*$this->n++;
			if ($this->n > 300){
				$this->src = false;
			}*/
		}
		return $this->tokens;
	}
}