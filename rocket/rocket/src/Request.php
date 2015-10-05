<?php
namespace Rocket;
/**
 * RocketPHP request.
 *
 * This class handles request data
 *
 * @package    Rocket
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Codename: BigBang ...where it all began...
 */

class Request {

	/**
	 * Root server path
	 * @var string
	 */
	private $rootPath;

	/**
	 * App path
	 * @var string
	 */
	private $appPath;

	/**
	 * Path to /public folder
	 * @var string
	 */
	private $publicPath;

	/**
	 * Relative path from $rootPath to $appPath
	 * @var [type]
	 */
	private $pathToRocket;

	/**
	 * Relative path from $rootPath to $publicPath
	 * @var [type]
	 */
	private $pathToPublic;

	/**
	 * Relative url from root to /public
	 * @var [type]
	 */
	private $urlPathToPublic;

	/**
	 * Current url without rewrites
	 * @var string
	 */
	private $url;

	/**
	 * Current uri path
	 * @var string
	 */
	private $uri;

	/**
	 * Current REST Method
	 * @var string
	 */
	private $method;

	/**
	 * Current request format
	 * Parsed from the extension in the url eg. xyz.json
	 * Defaults to html
	 * @var string
	 */
	private $format;

	/**
	 * Current protocol Eg. http or https
	 * @var string
	 */
	private $protocol;

	/**
	 * Url to site root (/public/index.php)
	 * @var string
	 */
	private $rootUrl;

	/**
	 * Request secure test based on http/https
	 * @var bool
	 */
	private $secure;

	/**
	 * Current query string
	 * @var [type]
	 */
	private $queryString;

	/**
	 * Current host name
	 * @var string
	 */
	private $host;

	/**
	 * Current port number
	 * @var int
	 */
	private $port;

	private $lang;

	/**
	 * Current request data
	 * All GET, POST and PUT data are merged
	 * into this variable in that order. POST vars
	 * will overwrite GET vars.
	 * @var array
	 */
	private $data;


	public $config = array();
	protected $defaults = array(
		//'allowed_formats' => 'html|json',
		//'default_format' => 'html',
		//'app_path' => '', // path to app root folder in filesystem
		'app_root' => '', // path from server root to app root
		'default_lang' => 'en_US'
	);


	/**
	 * Request constructor
	 * @param object $app Rocket app instance
	 */
	function __construct($config = array()){
		$this->config = array_merge($this->defaults, $config);
	}

	function setAppPaths($appPath, $publicPath)
	{
		$this->appPath = $appPath;
		$this->publicPath = $publicPath;

		$this->rootPath = $_SERVER['DOCUMENT_ROOT'];
		$this->pathToRocket = str_replace($this->rootPath, '', $this->appPath);
		$this->pathToPublic = $this->pathToRocket . str_replace($this->appPath, '', $this->publicPath);
		$this->urlPathToPublic = str_replace($this->rootPath, '', $this->pathToPublic);
	}

	/**
	 * Sets current url
	 * @param string $url Url value
	 */
	function setUrl($url){
		$this->url = $url;
	}

	/**
	 * Current url
	 * @return string Current url
	 */
	function url($withQueryString = true){
		if ($this->url === null){
			$format = $this->format();
			$queryString = $this->queryString();
			$this->url = $this->rootUrl() . $this->uri();
			if ($format){
				$this->url .= '.'.$format;
			}

			if ($withQueryString){
				$this->url .= (strlen($queryString) ? '?' . $queryString : '');
			}
		}
		return $this->url;
	}


	/**
	 * Set current root url
	 * @param string $url Root url value
	 */
	function setRootUrl($url){
		$this->rootUrl = $url;
	}

	/**
	 * Current root url value
	 * @return string Current root url
	 */
	function rootUrl(){
		if ($this->rootUrl === null){
			$url = trim(str_replace(DIRECTORY_SEPARATOR, '/', $this->protocol() . ($this->isSecure() ? 's' : '') . '://' . $this->host() . ($this->port() != 80 ? ':' . $this->port() : '') . $this->urlPathToPublic), ' /');
			$this->setRootUrl($url);
		}
		return $this->rootUrl;
	}

	/**
	 * Set current uri
	 * @param string $uri Current uri
	 */
	function setUri($uri){
		$uri = explode('?', $uri);
		$uri = $uri[0];
		// remove allowed formats stuff
		/*if (preg_match('/\.(.'.$this->config['allowed_formats'].')$/', $uri, $matches)){
			$this->format = $matches[1];
			$uri = str_replace($matches[0], '', $uri);
		}*/
		$app_root = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_PATH);
		$this->uri = '/' . trim(str_replace($app_root, '', $uri), ' /');

		/*$uri = explode('?', $uri);
		if (isset($uri[1])){
			$this->setQueryString($uri[1]);
		}

		$uri = str_replace($this->pathToPublic, '', $uri[0]);
		$this->uri = '/'.trim($uri, ' /');

		if (preg_match('/\.(.{2,4})$/', $this->uri, $matches)){
			$this->setFormat($matches[1]); // TODO: handle predefined formats from config to avoid errors on last param with dot. Eg. xyz.com/user/john@mail.com
			$this->uri = str_replace('.'.$this->format, '', $this->uri);
		}*/
	}

	/**
	 * Current uri
	 * @return string uri
	 */
	function uri(){
		/*$uri = explode('?', $_SERVER['REQUEST_URI']);
		$uri = $uri[0];
		if (preg_match('/\.(.'.$this->config['allowed_formats'].')$/', $uri, $matches)){
			$this->format = $matches[1];
			$uri = str_replace($matches[0], '', $uri);
		}
		return trim(str_replace($this->config['app_root'], '', $uri), ' /');*/

		if ($this->uri === null){
			$this->setUri($_SERVER['REQUEST_URI']);
		}
		return $this->uri;
	}

	/**
	 * Set current request method
	 * @param string $method Request method
	 */
	function setMethod($method){
		$this->method = strtoupper($method);
	}

	/**
	 * Current request method
	 * @return string Current request method
	 */
	function method(){
		if ($this->method === null){
			// allow overriding method during post
			if (count($_POST) && $this->data('REQUEST_METHOD')){
				$method = $this->data('REQUEST_METHOD');
			}else if(isset($_SERVER['REQUEST_METHOD'])){
				$method = $_SERVER['REQUEST_METHOD'];
			}else{
				$method = 'GET';
			}
			$this->setMethod($method);
		}
		return $this->method;
	}

	/**
	 * Set current request format
	 * @param string $format Format extension
	 */
	function setFormat($format){
		$this->format = $format;
	}

	/**
	 * Current request format
	 * @return string Request format
	 */
	function format(){
		$this->uri();
		if ($this->format === null){
			$this->setFormat($this->config['default_format']);
		}
		return $this->format;
	}

	/**
	 * Set current request protocol
	 * @param string $protocol Current request protocol
	 */
	function setProtocol($protocol){
		$this->protocol = $protocol;
	}

	/**
	 * Current request protocol
	 * @return string protocol
	 */
	function protocol(){
		if ($this->protocol === null){
			$protocol = $this->isSecure() ? 'https' : 'http';
			$this->setProtocol($protocol);
		}
		return $this->protocol;
	}

	/**
	 * Set current host name
	 * @param string $host Host name
	 */
	function setHost($host){
		// make sure host has no port number
		$this->host = preg_replace('/:[0-9]*$/', '', $host);
	}

	/**
	 * Current host name
	 * @return string Host name
	 */
	function host(){
		if ($this->host === null){
			$host = isset($_SERVER['HTTP_HOST']) ? 
						$_SERVER['HTTP_HOST'] : 
						(isset($_SERVER['SERVER_NAME']) ?
						$_SERVER['SERVER_NAME'] : 
						false);
			$this->setHost($host);
		}
		return $this->host;
	}

	/**
	 * Set request port number
	 * @param int $port Port number
	 */
	function setPort($port){
		$this->port = $port;
	}

	/**
	 * Current request port number
	 * @return int $port Port number
	 */
	function port(){
		if ($this->port === null){
			$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : false;
			$this->setPort($port);
		}
		return $this->port;
	}

	function setLang($lang)
	{
		$this->lang = $lang;
	}

	function lang(){
		if ($this->lang === null){
			if (isset($_GET['lang'])){
				$this->setLang($_GET['lang']);
			}else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
				$langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

				$accepted = array();
				foreach ($langs as $lang){
					$lang = explode(';', $lang);
					if (!isset($lang[1])){
						$lang[1] = 1;
					}else{
						$lang[1] = str_replace('q=', '', $lang[1]);
					}
					$accepted[$lang[1]] = str_replace('-', '_', $lang[0]);
				}
				ksort($accepted);
				$this->setLang(array_pop($accepted));
			}else{
				//'en_US'
				$this->setLang($this->config['default_lang']);
			}
		}
		return $this->lang;
	}

	/**
	 * Set request data
	 * @param array $data Array with request data
	 */
	function setData($data)
	{
		$this->data = $data;
		$_REQUEST = $data;
	}

	function persistData($data)
	{
		$_SESSION['persisted_data'] = serialize((array)$data);
	}

	/**
	 * Current request data
	 * @param  bool $key Data property name or false for all data
	 * @return mixed       Data value or array with all data
	 */
	function data($key = false)
	{
		if ($this->data === null){
			//parse_str($this->queryString(), $queryStringData); // TODO: test if this is needed, isn't it the same as $_GET?
			// TODO: handle content types here
			if (isset($_SERVER['CONTENT_TYPE']) && preg_match('/application\/json/', $_SERVER['CONTENT_TYPE'])){
				$jsonInput = json_decode(file_get_contents("php://input"), true);
				if ($jsonInput){
					$_POST = $jsonInput;
				}
				$_PUT_DELETE = array();
			}else{
				parse_str(file_get_contents("php://input"),$_PUT_DELETE);
			}

			$data = array_merge(array(), $_GET, $_POST, $_PUT_DELETE);

			/*if (isset($_SESSION['persisted_data'])){
				$data = array_merge($data, (array) unserialize($_SESSION['persisted_data']));
				unset($_SESSION['persisted_data']);
			}*/

			$this->setData($data);
			// TODO: hook here?
		}

		if ($key){
			if (isset($this->data[$key])){
				return $this->data[$key];
			}else{
				return null;
			}
		}
		return $this->data;
	}

	/**
	 * Push data into request data
	 * @param  array $data Array with new data
	 * @return null
	 */
	function pushData($data){
		//$this->trigger('push_data', array($this, &$data));
		$this->data = array_merge($this->data(), $data);
	}

	/**
	 * Set query string
	 * @param string $query Current query string
	 */
	function setQueryString($query){
		$this->queryString = $query;
	}

	/**
	 * Current query string
	 * @return string Query string value
	 */
	function queryString(){
		if ($this->queryString === null){
			$query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
			$this->setQueryString($query);
		}
		return $this->queryString;
	}

	/**
	 * Secure request test
	 * Based on request setting http/https only!
	 * Does not check if connection is actually secure
	 * @return boolean True if secure
	 */
	function isSecure(){
		// TODO: consider other methods to ensure security here
		if ($this->secure === null){
			$this->secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '');
		}
		return $this->secure;
	}

	/**
	 * Request is GET
	 * @return boolean True if is GET
	 */
	function isGet(){
		return $this->method === 'GET';
	}

	/**
	 * Request is POST
	 * @return boolean True if is POST
	 */
	function isPost(){
		return $this->method === 'POST';
	}

	/**
	 * Request is PUT
	 * @return boolean True if is PUT
	 */
	function isPut(){
		return $this->method === 'PUT';
	}

	/**
	 * Request is DELETE
	 * @return boolean True if is DELETE
	 */
	function isDelete(){
		return $this->method === 'DELETE';
	}

	/**
	 * Request is HEAD
	 * @return boolean True if is HEAD
	 */
	function isHead(){
		return $this->method === 'HEAD';
	}

	/**
	 * Request is OPTIONS
	 * @return boolean True if is OPTIONS
	 */
	function isOptions(){
		return $this->method === 'OPTIONS';
	}

	/**
	 * Request is PATCH
	 * @return boolean True if is PATCH
	 */
	function isPatch(){
		return $this->method === 'PATCH';
	}

	/**
	 * Request is via Ajax
	 * @return boolean True if is via Ajax
	 */
	function isAjax(){
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	function isMobile(){
		
	}

	function isRobot(){
		
	}

	/**
	 * Request is via CLI
	 * @return boolean True if is via CLI
	 */
	function isCli(){
		return (PHP_SAPI == 'cli');
	}

	function isFlash(){
		
	}

	/**
	 * Syntactic sugar method to call $request->method() as $request->method
	 * @param  string $name Method name
	 * @return mixed    See corresponding method for possible return values
	 */
	function __get($name){
		if (method_exists($this, $name)){
			return $this->$name();
		}
		trigger_error('Undefined property');
		return null;
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