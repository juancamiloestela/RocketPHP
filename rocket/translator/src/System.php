<?php
namespace Rocket\Translator;

include 'functions.php';

class System
{

	protected $request;
	static $translations = array();
	protected $lang;
	public $config = array();
	protected $defaults = array(
		'payload' => 'lang/',
		'default_lang' => 'en'
	);

	public function __construct($request, $config = array())
	{
		$this->config = array_merge($this->defaults, $config);
		$this->request = $request;

		$this->checkFolderStructure();

		$lang = $request->lang();
		if (!file_exists($this->config['payload'] . $lang . '.php')){
			$lang = substr($lang, 0, 2);
			if (!file_exists($this->config['payload'] . $lang . '.php')){
				$lang = $this->config['default_lang'];
			}
		}
		$this->lang = $lang;
		$this->loadTranslation($lang);
	}

	public function checkFolderStructure()
	{
		// TODO: this should happen to all systems, extend?
		if (!file_exists($this->config['payload'])){
			mkdir($this->config['payload'], 0755, true);
		}
	}

	public function loadTranslation($lang)
	{
		static::$translations = include $this->config['payload'] . $lang . '.php';
	}

	public static function translate($key)
	{
		if (isset(static::$translations[$key])){
			return static::$translations[$key];
		}
		return $key;
	}
}