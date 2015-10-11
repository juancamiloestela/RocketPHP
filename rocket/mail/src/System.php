<?php
namespace Rocket\Mail;

include 'Mail.php';

class System
{

	public $config = array();
	protected $defaults = array(
		'payload' => false
	);

	public function __construct($config = array())
	{
		$this->config = array_merge($this->defaults, $config);

		$this->checkFolderStructure();
	}

	public function checkFolderStructure()
	{
		// TODO: this should happen to all systems, extend?
		if ($this->config['payload'] && !file_exists($this->config['payload'])){
			mkdir($this->config['payload'], 0755, true);
		}
	}

	public function make($message = false, $alt = false){
		$instance = new Mail($this->config);
		if ($message){
			$instance->message($message, $alt);
		}
		return $instance;
	}
}