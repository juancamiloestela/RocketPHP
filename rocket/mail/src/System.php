<?php
namespace Rocket\Mail;

include 'Mail.php';

class System
{

	protected $template;
	public $config = array();
	protected $defaults = array(
		'payload' => 'emails/'
	);

	public function __construct($template, $config = array())
	{
		$this->config = array_merge($this->defaults, $config);
		$this->template = $template;
	}

	public function __invoke($message = false, $alt = false)
	{
		return $this->make($message, $alt);
	}

	public function send($mail, $data = array())
	{
		$filename = $this->config['payload'] . $mail . '.php';
		if (!file_exists($filename)){
			throw new \Exception('Mail file '.$mail.' not found');
		}
		include $filename;
	}

	public function make($message = false, $alt = false){
		$instance = new Mail();
		if ($message){
			$instance->message($message, $alt);
		}
		return $instance;
	}
}