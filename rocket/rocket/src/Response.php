<?php
namespace Rocket;
/**
 * RocketPHP response.
 *
 * This class handles response delivery
 *
 * @package    Rocket
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Codename: BigBang ...where it all began...
 */

class Response {

	/**
	 * Response status
	 * @var int
	 */
	private $status = 200;

	/**
	 * Response body
	 * @var string
	 */
	private $body = '';

	/**
	 * Response headers
	 * @var array
	 */
	private $headers = array(
		'Content-Type' => 'text/html; charset=utf-8'
	);

	/**
	 * HTTP response codes
	 * @var array
	 */
	private $http = array(
		100 => 'HTTP/1.1 100 Continue',
		101 => 'HTTP/1.1 101 Switching Protocols',
		200 => 'HTTP/1.1 200 OK',
		201 => 'HTTP/1.1 201 Created',
		202 => 'HTTP/1.1 202 Accepted',
		203 => 'HTTP/1.1 203 Non-Authoritative Information',
		204 => 'HTTP/1.1 204 No Content',
		205 => 'HTTP/1.1 205 Reset Content',
		206 => 'HTTP/1.1 206 Partial Content',
		300 => 'HTTP/1.1 300 Multiple Choices',
		301 => 'HTTP/1.1 301 Moved Permanently',
		302 => 'HTTP/1.1 302 Found',
		303 => 'HTTP/1.1 303 See Other',
		304 => 'HTTP/1.1 304 Not Modified',
		305 => 'HTTP/1.1 305 Use Proxy',
		307 => 'HTTP/1.1 307 Temporary Redirect',
		400 => 'HTTP/1.1 400 Bad Request',
		401 => 'HTTP/1.1 401 Unauthorized',
		402 => 'HTTP/1.1 402 Payment Required',
		403 => 'HTTP/1.1 403 Forbidden',
		404 => 'HTTP/1.1 404 Not Found',
		405 => 'HTTP/1.1 405 Method Not Allowed',
		406 => 'HTTP/1.1 406 Not Acceptable',
		407 => 'HTTP/1.1 407 Proxy Authentication Required',
		408 => 'HTTP/1.1 408 Request Time-out',
		409 => 'HTTP/1.1 409 Conflict',
		410 => 'HTTP/1.1 410 Gone',
		411 => 'HTTP/1.1 411 Length Required',
		412 => 'HTTP/1.1 412 Precondition Failed',
		413 => 'HTTP/1.1 413 Request Entity Too Large',
		414 => 'HTTP/1.1 414 Request-URI Too Large',
		415 => 'HTTP/1.1 415 Unsupported Media Type',
		416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
		417 => 'HTTP/1.1 417 Expectation Failed',
		500 => 'HTTP/1.1 500 Internal Server Error',
		501 => 'HTTP/1.1 501 Not Implemented',
		502 => 'HTTP/1.1 502 Bad Gateway',
		503 => 'HTTP/1.1 503 Service Unavailable',
		504 => 'HTTP/1.1 504 Gateway Time-out',
		505 => 'HTTP/1.1 505 HTTP Version Not Supported',
	);

	private $request;
	public $config = array();
	protected $defaults = array(

	);

	/**
	 * Response constructor
	 * @param object $app Rocket app instance
	 */
	function __construct($request, $config = array()){
		$this->config = array_merge($this->defaults, $config);
		$this->request = $request;
	}

	/**
	 * Response status getter/setter
	 * @param  int $status Response status code
	 * @return int         Response status code
	 */
	function status($status = null){
		if ($status !== null){
			$this->status = $status;
			return $this;
		}
		return $this->status;
	}

	function statusMessage(){
		return substr($this->http[$this->status()], 13);
	}

	/**
	 * Push header into headers stack
	 * @param  type $type  Header type
	 * @param  value $value Header value
	 * @return null
	 */
	function header($type, $value){
		$this->headers[$type] = $value;
	}

	/**
	 * Response body getter/setter
	 * @param  string $body Response body
	 * @return string       Response body
	 */
	function body($body = null)
	{
		if ($body !== null){
			$this->body = $body;
			return $this;
		}
		return $this->body;
	}

// TODO: unify this behavior setter,getter,etc

	/*function data($key = false, $value = UNDEFINED)
	{
		if (is_array($key) || $key instanceof \Traversable){
			// data can be completely overridden, if you need to 
			// preserve old data set by other systems you must merge the data
			// before setting it, or, setting individual key value pairs
			$this->data = $key;
			return $this;
		}
		if ($key){
			if ($value != UNDEFINED){
				// setting a key - value
				$this->data[$key] = $value;
				return $this;
			}else{
				// getting a key - value
				if (isset($this->data[$key])){
					return $this->data[$key];
				}
				return null;
			}
		}
		return $this->data;
	}*/

	/*function signal($key = false, $value = UNDEFINED)
	{
		if (is_array($key) || $key instanceof \Traversable){
			// if an array is passed, data is merged. Previous data is
			// preserved, you must explicitly override the desired value
			// this protects other systems signal data
			$this->signal = array_merge($this->signal, (array)$key);
			return $this;
		}
		if ($key){
			if ($value !== UNDEFINED){
				// setting a key - value
				$this->signal[$key] = $value;
				return $this;
			}else{
				// getting a key - value
				if (isset($this->signal[$key])){
					return $this->signal[$key];
				}
				return null;
			}
		}
		return $this->signal;
	}*/

	/**
	 * Redirect browser to another location
	 * @param  string  $url  Target url
	 * @param  int $code Redirect code
	 * @return null
	 */
	function redirect($url, $code = 303)//, $data = false)
	{
		//$this->trigger('redirect', array($this));

		// if url is relative, therefore internal...
		if (strpos($url, '/') === 0){
			$url = APP_URL . trim($url, ' /');
			/*if (!$format){
				$format = $this->app->request->format();
			}
			$format = '.'.$format;

			// if url doesn't have the chosen format, add it
			if (stripos($url, $format) === false && $format != '.html'){
				$url = explode('?', $url);
				$url[0] .= $format;
				$url = implode('?', $url);
			}*/
		}

		//if ($data){
			//$this->app->request->persistData($data);
		//}

		if ($this->request->format() != 'html'){
			$url = $url . '.' . $this->request->format();
		}

		// go for it!
		header('Location: ' . $url, true, $code);
		die('response redirected to '.$url);
	}


	/*function redirectIfFormatIs($format, $url, $code = 303, $redirectFormat = null)
	{
		if ($this->app->request->format() == $format){
			$this->redirect($url, $code, $redirectFormat);
		}
	}*/

	/**
	 * Send response to browser
	 * Sets headers and echoes the response body
	 * @return null
	 */
	function send(){
		header($this->http[$this->status], true, $this->status);
		//$this->trigger('prepare_headers', array($this));
		foreach ($this->headers as $type => $value){
			header($type . ': ' . $value, true);
		}

		//$this->trigger($this->status, array($this));
		//$this->trigger('send', array($this));

		echo $this->body;
		die();
	}

	function __toString(){
		return $this->send();
	}
}

/**
 * Copyright (c) 2014 Juan Camilo Estela <juank@revolutiondynamics.co>, others
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */