<?php
namespace Rocket\Template;
/**
 * RocketTemplateSystem
 *
 * Template System for RocketPHP Framework
 *
 * @author  Juan Camilo Estela <juancamiloestela@revolutiondynamics.co>
 * @version  0.1
 */


//include 'Parser.php';
//include 'Lexer.php';
//include 'Renderer.php';

class System
{
	
	public $config = array();
	protected $defaults = array(
		'label_templates' => true,
		'force_recompile' => true,
		'payload' => 'templates/',
		'cache_path' => 'cache/'
	);

	public $data = array();
	public $parser;
	public $repeats = 0;


	function __construct($config = array())
	{
		$this->config = array_merge($this->defaults, $config);
		$this->config['cache_path'] = $this->config['payload'] . $this->config['cache_path'];
	}

	/*public function call($response)
	{
		// let all other systems do their thing
		$this->propagate();

		$template = $response->signal('template');

		// TODO: for next versions, which is best?
		// $response->data('template')
		// $response->signal() using this one for the moment, keeps runtime data protected as it cannot be overriden by input data
		// $response->control()
		// $request->signal()
		// $signal->data('template')
		$this->trigger('template', array(&$template));

		// if signal('template') has been explicitly set to false, bypass
		// template engine. If null then no template signal has been set and 
		// template engine will not be bypassed
		if ($template !== false){
			$response->body($this->render($template, $response->data()));
		}
	}*/

	public function render($file, $data = array())
	{
		// lazy init
		if (!$this->parser){
			include 'Parser.php';
			include 'Lexer.php';
			include 'Renderer.php';
			include 'TemplateDataObject.php';

			$renderers = include 'renderers.php';
			$this->parser = new Parser(new Lexer(), new Renderer($renderers, $this));
		}

		if (empty($file)){
			throw new \Exception('Template filename is empty.');
		}

		$this->data = new TemplateDataObject($data, \ArrayObject::ARRAY_AS_PROPS);

		// inject constants into template data
		$constants = get_defined_constants(true);
		$constants = $constants['user'];
		foreach ($constants as $name => $constant){
			$this->data[$name] = $constant;
		}

		$compiledFile = $this->config['cache_path'] . $file;
		$sourceFile = $this->config['payload'] . $file;

		$this->compileFile($sourceFile, $compiledFile);
		$rendered = $this->exec($compiledFile);

		// make paths work in subfolder installations
		// eg. localhost/client/project/index.php
		//$rendered = str_replace('href="/', 'href="'.APP_ROOT, $rendered);
		//$rendered = str_replace('src="/', 'src="'.APP_ROOT, $rendered);
		//$rendered = str_replace('action="/', 'action="'.APP_ROOT, $rendered);
		// todo: handle protocol-less src's eg. src="//jquery..."

		return $rendered;
	}

	protected function exec($compiledFile){
		ob_start();
		extract($this->data->toArray());
		$engine = $this;
		include $compiledFile;
		$rendered = ob_get_contents();
		ob_end_clean();
		return $rendered;
	}

	public function compileFile($sourceFile, $compiledFile)
	{
		if (!file_exists($compiledFile) || 
			$this->config['force_recompile'] || 
			(filemtime($sourceFile) > filemtime($compiledFile))) {

			if (!file_exists($sourceFile)){
				throw new \Exception('Template "'.str_replace($this->config['app_path'], '', $sourceFile).'" does not exist');
			}

			$source = file_get_contents($sourceFile);
			$slug = str_replace(array($this->config['payload'], '.php'), '', $sourceFile);

			$compiled = $this->compile($source);

			if (is_array($compiled)){
				$this->write($compiled['layout']['path'], $compiled['layout']['output']);
				$blocks = array();
				foreach ($compiled['blocks'] as $block){
					$blocks[] = '"'.$block['name'].'" => "' . $slug . '_' . $block['name'] . '.php"';
					$this->write($slug . '_' . $block['name'] . '.php', $block['output']);
				}
				$page = '<?php $_blocks = array('.implode(',', $blocks).'); include $this->config["cache_path"] . "' . $compiled['layout']['path'].'";';
				$this->write($slug . '.php', $page);
			}else{
				$this->write($slug . '.php', $compiled);
			}
			return true;
		}
		return false;
	}

	public function compile($source){
		$compiled = array(
			'layout' => array(),
			'blocks' => array()
		);

		if (preg_match('/<extends +layout="([a-zA-Z0-9_\.\/-]+)" *\/?>/', $source, $match)){
			preg_match_all('/<block +name="([a-zA-Z0-9_\.-]+)" *\/?>(.+?)<\/block>/s', $source, $matches, PREG_SET_ORDER);

			// compile layout
			//$layout = file_get_contents($this->config['app_path'] . $match[1]);
			// TODO: throw exception if no layout file is found, also check blocks

			$layoutPath = $this->config['payload'] . $match[1];
			$layout = file_get_contents($layoutPath);

			$compiled['layout'] = array(
				'output' => $this->parser->parse($layout),
				'path' => $match[1]
			);

			// compile all blocks
			foreach ($matches as $block){
				$parsed = $this->parser->parse($block[2]);
				if ($this->config['label_templates']){
					$parsed = '<!-- '.$block[1].' -->' . $parsed . '<!-- END '.$block[1].' -->';
				}
				$compiled['blocks'][$block[1]] = array(
					'output' => $parsed,
					'name' => $block[1]
				);
			}

			return $compiled;
		}else{
			return $this->parser->parse($source);
		}
	}

	private function write($path, $content)
	{
		$path = $this->config['cache_path'] . $path;
		//echo 'writing '.$path.'<br>';

		if (!file_exists(pathinfo($path, PATHINFO_DIRNAME))){
			mkdir(pathinfo($path, PATHINFO_DIRNAME), 0777, true);
		}
		return file_put_contents($path, $content);
	}

	function e($var){
		echo $this->v($var);
	}

	function v($keys){
		$value = $this->data;

		foreach ($keys as $key){
			if (is_array($value) && isset($value[$key])){
				$value = $value[$key];
			}else if (is_object($value) && isset($value->$key)){
				$value = $value->$key;
			}else if ($value instanceof \Rocket\Model\Relation){
				// model relation
				if (is_numeric($key)){
					// TODO: avoid having to query all relations
					//$o = $value->get($key);
					$value = $value[$key];
				}else{
					$value = $value->$key;
				}
			}else{
				$value = null;
				break;
			}
		}

		if ($value instanceof \Rocket\Model\Relation){
			$value = $value->get();
		}

		return $value;
	}

	function _invoke($content, $data = array())
	{
		return $this->render($content, $data);
	}

}