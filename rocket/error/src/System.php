<?php 
namespace Rocket\Error;

/**
 * Although display_errors may be set at runtime (with ini_set()), it won't have
 * any affect if the script has fatal errors. This is because the desired runtime action does not get executed.
 */

class System
{

	private $error = false;

	public $config = array();
	protected $defaults = array(
		'payload' => 'errors/',
		'show_errors' => true,
		'log_errors' => true,
		'error_log' => 'log'
	);

	public function __construct($config = array())
	{
		$this->config = array_merge($this->defaults, $config);
		ob_start();

		$this->checkFolderStructure();

		if ($this->config['log_errors']){
			ini_set('log_errors', 1);
			ini_set('error_log', $this->config['payload'] . DIRECTORY_SEPARATOR . $this->config['error_log']);
		}

		if ($this->config['show_errors']){
			ini_set('display_errors', 1);
			ini_set("track_errors", 1);
			ini_set("html_errors", 1);
			error_reporting(E_ALL | E_STRICT);
		}else{
			error_reporting(0);
			if (function_exists('xdebug_disable')){
				xdebug_disable();
			}
			// TODO: send mail on fatal errors
		}

		set_error_handler(array($this, 'handleError'));
		set_exception_handler(array($this, 'handleException'));
		register_shutdown_function(array($this,'shutdownHandler'), $this);
	}

	public function checkFolderStructure()
	{
		// TODO: this should happen to all systems, extend?
		if (!file_exists($this->config['payload'])){
			mkdir($this->config['payload'], 0755, true);
		}
	}

	public function shutdownHandler(&$instance)
	{
		$error = error_get_last();
		if ($error){
			// If fatal error
			// fake a debug_backtrace response
			// with data from error_get_last
			if (!$this->error){
				$this->error = new FatalException($error['message'], 0, $error['type'], $error['file'], $error['line']);
			}
		}

		if ($this->error){// && in_array($error['type'], $fatalErrors)){
			// clear all open buffers to avoid showing
			// partial generated content
			$partialContent = '';
			while(ob_get_level()){
				$partialContent .= ob_get_contents();
				ob_end_clean();
			}
			// show the error with data pulled from instance
			//$this->showError($instance->getBacktrace(), $partialContent);
			$this->showError($this->error, $partialContent);
		}
	}

	public function showError($e, $partial = null)
	{
		header('HTTP/1.1 500 Internal Server Error');
		$error = array(
			'file' => isset($e->_file) ? $e->_file : $e->getFile(),
			'line' => isset($e->_line) ? $e->_line : $e->getLine(),
			'message' => $e->getMessage(),
			'trace' => $e->getTrace()
		);

		// get code sample
		$lines = file($error['file']);
		array_unshift($lines, null); // make line count start in 1, not 0
		$start = $error['line'] - 5;
		$start = $start > 0 ? $start : 0;
		$code = array_slice($lines, $start, 10, true);

		ob_start();
		include(__DIR__  . '/template.php');
		$template = ob_get_clean();

		echo $template;
	}

	public function handleError($level, $string, $file = null, $line = null, $context)
	{
		throw new \ErrorException($string, 0, $level, $file, $line);
	}

	public function handleException(\Exception $e)
	{
		$this->error = $e;
		die();
	}

	public function __invoke($key, $value = false)
	{
		
	}

	public function shutdown($content)
	{
		return $content;
	}

}

class FatalException
{
	private $line;
	private $level;
	private $message;
	private $file;

	public function __construct($message, $code, $level, $file, $line){
		$this->message = $message;
		$this->level = $level;
		$this->file = $file;
		$this->line = $line;
	}

	public function getMessage(){
		return $this->message;
	}

	public function getLine(){
		return $this->line;
	}

	public function getFile(){
		return $this->file;
	}

	public function getTrace(){
		return array(
			array(
				'file' => $this->file,
				'line' => $this->line,
				'function' => '',
				'type' => $this->level,
				'class' => '',
				'args' => array()
			)
		);
	}
}


/*
This exception wraps the default exception but allows users
to customize the line and file of the error. This is usefull
for example in template error systems where we want to show the 
error on the template instead of on the template class.
 */
class Exception extends \Exception
{
	public $_line;
	public $_file;

	public function __construct($message, $code = 0, Exception $previous = null, $file = null, $line = null)
	{
		parent::__construct($message, $code, $previous);
		if ($file){
			$this->_file = $file;
		}
		if ($line){
			$this->_line = $line;
		}
	}
}