<?php 
/*function buildChain($chain){
	$chain = trim($chain, ' $;');
	$chain = str_replace(array('->','[',']'), '.', $chain);
	$chain = explode('.', $chain);

	$parsed = array();
	foreach ($chain as $link){
		if ($link == ''){
			continue;
		}else if (stripos($link, '$') === 0){
			$parsed[] = $link;
		}else if (is_numeric($link)){
			$parsed[] = $link;
		}else{
			$parsed[] = '"'.$link.'"';
			/*if (count($parsed)){
				$parsed[] = '"'.$link.'"';
			}else{
				$parsed[] = '$'.$link;
			}* /
		}
	}
	//var_dump('array('.implode(',',$parsed).')');
	return 'array('.implode(',',$parsed).')';
}*/
if (!function_exists('parseVarname')){
	function parseVarname($src){
		// make sure we don't replace decimals for '->'
		$src = preg_replace('/([0-9])?\.([0-9])/', '$1__DECIMAL__$2', $src);
		// replace dot notation for php object notation and eliminate ;
		$chain = explode('->',str_replace(array('.',';'), array('->',''), $src));
		$varname = array();
		foreach ($chain as $link){
			// chain link has a - in it, so it must be called as an array key
			if (stripos($link, '-') !== false){
				$varname[] = "['".$link."']";
			}else{
				if (count($varname)){
					$varname[] = '->'.$link;
				}else{
					$varname[] = $link;
				}
			}
		}
		// return decimals back to . and concat every chain link
		return str_replace('__DECIMAL__', '.', implode('', $varname));
	}
}

return array(
	'DOLLAR' => function($token){
		return '$';
	},
	'VAR' => function($token){
		// TODO: make this configurable so that placeholder syntax can be changed
		// at the moment only $varname; and {{varname}} are supported
		//buildChain($token['text']);
		//$varname = str_replace(array('$',';','{{','}}',']->','->','[',']'), array('','','','','.','.','.','.'), $token['text']);
		//$varname = explode('.', $varname);
		/*return '<?php $this->e(["'.implode('","', $varname).'"]); ?>';*/
		/*return '<?php $engine->e('.buildChain($token['text']).'); ?>';*/
		/*return '<?php echo '.str_replace('.','->',$token['text']).'; ?>';*/
		//$varname = str_replace(array(';','.','-'), array('','->','_'), $token['text']);
		$varname = parseVarname($token['text']);
		if (substr($varname, -1) == ')'){
			return '<?php echo '.$varname.'; ?>';
		}else{
			return '<?php echo $this->data->'.substr($varname, 1).'; ?>';
			//return '<?php if (isset('.$varname.')){echo '.$varname.';} ? >';
		}
	},
	'TAB' => function($token){
		return $token['text'];
	},
	'WHITESPACE' => function($token){
		return $token['text'];
	},
	'NEWLINE' => function($token){
		return $token['text'];
	},
	'TEMPLATE_COMMENT' => function(){
		return '';
	},
	'PHP_TAG' => function($token){
		return $token['text'];
	},
	'EXTENDS_TAG' => function($token){
		$extend = '';

		if (isset($token['attributes']['layout'])){

		}
		return 'EXTENDED';
	},
	'BLOCK_TAG' => function($token){
		return '<?php if (isset($_blocks["'.$token['attributes']['name'].'"])){ include $this->config["cache_path"] . $_blocks["'.$token['attributes']['name'].'"];} ?>';
	},
	'INCLUDE_TAG' => function($token){
		$include = '';
		if (isset($token['attributes']['file'])){
			if (isset($token['attributes']['compile']) && $token['attributes']['compile'] == 'true'){
				$this->compileFile($this->config['payload'] . $token['attributes']['file'], $this->config['cache_path'] . $token['attributes']['file']);
				$include = '<?php include $this->config["cache_path"] . "'.$token['attributes']['file'].'"; ?>';
			}else{
				$include = '<?php include $this->config["payload"] . "'.$token['attributes']['file'].'"; ?>';
			}
		}
		return $include;
	},
	'TEMPLATE_TAG' => function($token){
		$template = '';
		if (isset($token['attributes']['src'])){
			//$this->compileFile($this->config['app_path'] . $token['attributes']['src'], $this->config['app_path'] . 'cache/'.$token['attributes']['src']);
			
			$this->compileFile($this->config['payload'] . $token['attributes']['src'], $this->config['cache_path'] . $token['attributes']['src']);
			$args = array_keys($token['attributes']);
			array_unshift($args, 'engine');
			//$values = array('$this->config("app_path")');
			$values = array('$engine');
			foreach ($token['attributes'] as $value){
				if (stripos($value, '$') !== 0){
					$values[] = '"'.$value.'"';
				}else{
					/*$value = str_replace('$', '', $value);
					$value = str_replace(array('[',']','->'),'.',$value);
					$value = explode('.', $value);*/
					//$values[] = '$engine->v('.buildChain($value).')';
					//$value = str_replace(array('.','-'),array('->','_'), $value);
					$value = parseVarname($value);
					//$values[] = '($_ =& '.$value.' ?: null)';
					$values[] = '(isset('.$value.') ? '.$value.' : null)';
				}
			}
			//$template = '<?php call_user_func(function($'.implode(', $', $args).') { include $engine->config("app_path") . "cache/" . $src;}, '.implode(',', $values).'); ? >';
			$template = '<?php call_user_func(function($'.implode(', $', $args).') { include $engine->config["cache_path"] . $src;}, '.implode(',', $values).'); ?>';
		}
		return $template;
	},
	'REPEAT_TAG' => function($token){
		$repeat = '<?php ';

		if (isset($token['attributes']['foreach'])){
			$repeat .= $token['attributes']['foreach'].' = isset('.$token['attributes']['foreach'].') ? '.$token['attributes']['foreach'].' : array();';
// TODO: organize first-last-total logic, its too ugly
$identifier = str_replace(array('->','.'), '', $token['attributes']['foreach']);
$repeat .= $identifier.'_count = -1;';
if (isset($token['attributes']['total'])){
	$total = $token['attributes']['total'];
}else{
	$total = $identifier.'_total';
}
$repeat .= $total.' = count('.$token['attributes']['foreach'].');';

			$token['attributes']['foreach'] = str_replace('.', '->', $token['attributes']['foreach']);
			$repeat .= 'foreach ('.$token['attributes']['foreach'].' as ';
			if (isset($token['attributes']['key'])){
				$repeat .= $token['attributes']['key'].' => ';
			}
			$repeat .= $token['attributes']['value'].'):';
			$repeat .= '$engine->data["'.substr($token['attributes']['key'],1).'"] = '.$token['attributes']['key'].'; $engine->data["'.substr($token['attributes']['value'],1).'"] = '.$token['attributes']['value'].';';
		}else if (isset($token['attributes']['from'])){
			$repeat .= '$range_'.$this->repeats.' = range((int)'.$token['attributes']['from'].',(int)'.$token['attributes']['to'].');';
// TODO: organize first-last-total logic, its too ugly
$identifier = '$range_'.$this->repeats;
$repeat .= $identifier.'_count = -1;';
if (isset($token['attributes']['total'])){
	$total = $token['attributes']['total'];
}else{
	$total = $identifier.'_total';
}
$repeat .= $total.' = count($range_'.$this->repeats.');';

			$repeat .= ' foreach ($range_'.$this->repeats.' as ';
			if (isset($token['attributes']['index'])){
				$repeat .= $token['attributes']['index'].' => ';
			}
			$repeat .= $token['attributes']['var'].'): ';
			if (isset($token['attributes']['index'])){
				$repeat .= '$engine->data["'.substr($token['attributes']['index'],1).'"] = '.$token['attributes']['index'].';';
			}
			$repeat .= '$engine->data["'.substr($token['attributes']['var'],1).'"] = '.$token['attributes']['var'].';';
			$this->repeats++;
		}else if (isset($token['attributes']['loops'])){
			$repeat .= '$range_'.$this->repeats.' = range(1,(int)'.$token['attributes']['loops'].');';
// TODO: organize first-last-total logic, its too ugly
$identifier = '$range_'.$this->repeats;
$repeat .= $identifier.'_count = -1;';
if (isset($token['attributes']['total'])){
	$total = $token['attributes']['total'];
}else{
	$total = $identifier.'_total';
}
$repeat .= $total.' = count($range_'.$this->repeats.');';

			$repeat .= ' foreach ($range_'.$this->repeats.' as ';
			$repeat .= $token['attributes']['loop'].'):';
			$repeat .= '$engine->data["'.substr($token['attributes']['loop'],1).'"] = '.$token['attributes']['loop'].';';
			$this->repeats++;
		}

		// handle first and last flags
		if (isset($token['attributes']['first']) || isset($token['attributes']['last'])){
			$repeat .= $identifier.'_count++;';
		}
		if (isset($token['attributes']['first'])){
			$repeat .= $token['attributes']['first'] . ' = ('.$identifier.'_count === 0);';
		}
		if (isset($token['attributes']['last'])){
			$repeat .= $token['attributes']['last'] . ' = ('.$identifier.'_count === '.$total.' - 1);';
		}

		$repeat .= ' ?>';
		return $repeat;
	},
	'REPEAT_CLOSE_TAG' => function($token){
		return '<?php endforeach; ?>';
	},
	'IF_TAG' => function($token){
		$if = '';

		if (isset($token['attributes']['condition'])){
			$if = '<?php if ('.str_replace('.','->',$token['attributes']['condition']).'): ?>';
		}
		return $if;
	},
	'ELSE_TAG' => function($token){
		return '<?php else: ?>';
	},
	'ELSEIF_TAG' => function($token){
		return '<?php elseif ('.str_replace('.','->',$token['attributes']['condition']).'): ?>';
	},
	'IF_CLOSE_TAG' => function($token){
		return '<?php endif; ?>';
	},

	'TEXT' => function($token){
		return $token['text'];
	}
);