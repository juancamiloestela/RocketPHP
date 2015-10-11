<?php

/**
 * Template Parser
 *
 * Template System for RocketPHP Framework
 *
 * @author  Juan Camilo Estela <juancamiloestela@revolutiondynamics.co>
 * @version  0.1
 */
namespace Rocket\Template;

class Parser{

	protected $renderer;
	protected $lexer;
	protected $compiled = '';
	static $open = '\$';
	static $close = ';?';

	function __construct($lexer, $renderer){
		$this->lexer = $lexer;
		$this->renderer = $renderer;
	}

	function parse($source){
		$tokens = $this->lexer->lex($source, array(
			'DOLLAR' => '\\\\\$',
			//'VAR' => '\$[a-zA-Z0-9_\.>\[\]\(\)\$-]+[a-zA-Z0-9_\]\)];?',
			// TODO: allow $a->b()                                   (\([^\)]*\)|\[[^\]]+\])*
			'VAR' => static::$open . '[_a-zA-Z\$]([_a-zA-Z0-9->.]*(\[[^\]]+\]\]*|\([^\)]*\)\)*)*)*' . static::$close,
			//'VAR' => '\$[a-zA-Z_\.>\$-]+[a-zA-Z0-9_]*(\([^\)]*\)+)?(\[[^\]]\]+)?;?', //'\{\{[a-zA-Z0-9_\.>\[\]\(\)-]+\}\}' TODO: make configurable
			'TAB' => '\t+',
			'WHITESPACE' => ' +',
			'NEWLINE' => '(\n\r?)+',
			'TEMPLATE_COMMENT' => '\/#.+?#\/',
			'PHP_TAG' => '<\?php ?.+?\?>',
			'EXTENDS_TAG' => '<extends ([a-zA-Z0-9_-]+="[^"]+" *)+ *\/?>',
			'BLOCK_TAG' => '<block ([a-zA-Z0-9_-]+="[^"]+" *)+ *\/?>',
			'INCLUDE_TAG' => '<include ([a-zA-Z0-9_-]+="[^"]*" *)+ *\/?>',
			'TEMPLATE_TAG' => '<template ([a-zA-Z0-9_-]+="[^"]*" *)+ *\/?>',
			'REPEAT_TAG' => '<repeat ([a-zA-Z0-9_-]+="[^"]+" *)+ *>', //'<repeat [^>]+>', // TODO: allow $a->b and $a->c()
			'REPEAT_CLOSE_TAG' => '<\/repeat>',
			'IF_TAG' => '<if ([a-zA-Z0-9_-]+="[^"]*" *)+ *>',
			'ELSE_TAG' => '<else>',
			'ELSEIF_TAG' => '<elseif ([a-zA-Z0-9_-]+="[^"]*" *)+ *>',
			'IF_CLOSE_TAG' => '<\/if>',
			
			//'TEXT' => '.+?(?=\$|<\/?repeat|<\/?if|<\/?extends|<\/?block|\/#|<\?php|$)'
			'TEXT' => '.'
		));

		$compiled = '';
		foreach ($tokens as $token){
			$token['attributes'] = isset($token['attributes']) ? $token['attributes'] : array();
			preg_match_all('/([a-z0-9-]+)="([^"]*)"/', $token['text'], $matches, PREG_SET_ORDER);
			foreach ($matches as $match){
				$token['attributes'][$match[1]] = $match[2];
			}
			$compiled .= $this->renderer->render($token);
		}
		return $compiled;
	}
}