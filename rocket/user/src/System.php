<?php
namespace Rocket\User;
/**
 * User System to handle users in RocketPHP.
 *
 * This system will handle user accounts
 * in RocketPHP apps.
 *
 * @package    Rocket\User
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Initial implementation
 */

include 'Helpers.php';
//include 'Attempts.php';
//include 'User.php';

class System
{

	private $class;
	public $current = false;

	// Required systems
	protected $request;
	protected $database;
	protected $validation;

	protected static $instance;

	protected $validator;

	public $config = array();
	protected $defaults = array(
		'payload' => false,
		'force_account_verification' => false,
		'verification_lifespan' => 'P1D', // Period: 1 Day, see dateinterval docs
		'request_lifespan' => 'PT5M', // Period: 1 min, see dateinterval docs
		'reset_password_lifespan' => 'PT1H', // Period Time: 1 hour, see dateinterval docs
		//'user_class' => '\Rocket\User\User',
		'user_table' => 'users'
		//,'check_request_signature' => true, // allows auth only via public key
	);

	//public function __construct($request, $database, $validation, $config = array()){
	public function __construct($database, $request, $config = array()){
		$this->config = array_merge($this->defaults, $config);
		$this->database = $database;
		$this->request = $request;

		static::$instance = $this;

		$this->init();
		register_shutdown_function(array($this, 'shutdown'));
	}
	
	public function init()
	{
		if (!isset($_SESSION)){
			session_start();
		}

		if ($this->request->data('pk')){
			// Identify current user by public/private key
			$this->current = $this->getUserByPublicKey($this->request->data('pk'));
			// If user exists, check if request is signed
			if ($this->current){
				// If signed, check signature
				if ($this->request->data('sig')){
					// check request age
					$now = new \DateTime('now');
					$ts = '0000-00-00 00:00:00';
					// Expects ts to be YYYY-MM-DD HH:MM:SS
					if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $this->request->data('ts'))){
						$ts = rawurldecode($this->request->data('ts'));
					}
					$requestTime = (new \DateTime($ts))->add(new \DateInterval($this->config['request_lifespan']));

					// check if signed request is expired
					if ($requestTime >= $now){
						// check signature
						$signature = $this->getRequestSignature($this->request, $this->current->private_key);

						if (!$signature || $signature !== rawurlencode($this->request->data('sig'))){
							$this->current = null;
						}else{
							// Auth via tokens is supposed to be stateless 
							// $_SESSION['user.logged'] = true;
						}
					}else{
						$this->current = null;
					}
				}else{
					$this->current = null;
				}
			}
		}else{
			// Identify current user by session key
			$id = isset($_SESSION['user.id']) ? $_SESSION['user.id'] : false;

			if ($id){
				$this->current = $this->getUserById($id);
			}
		}

		if (!$this->current){
			$this->current = null;
		}
	}

	function shutdown()
	{
		if ($this->isLogged()){
			$_SESSION['user.id'] = $this->current->id;
		}else{
			$_SESSION['user.id'] = null;
		}
	}

	static function on_properties(&$properties)
	{
		$spec = json_decode('{
				"email": {"type": "string", "max_length": 50, "unique": true},
				"password": {"type": "string", "min_length": 6, "db": {"type": "CHAR(60)"}},
				"public_key": {"type": "string", "max_length": 50},
				"private_key": {"type": "string", "max_length": 50},
				"role": {"type": "string", "max_length": 50},
				"active": {"type": "bool", "default": 1},
				"verified": {"type": "bool"},
				"verification_code": {"type": "string", "max_length": 60, "min_length": 60},
				"verification_time": {"type": "datetime"},
				"verification_expiration": {"type": "datetime"},
				"reset_code": {"type": "string", "max_length": 60, "min_length": 60},
				"reset_expiration": {"type": "datetime"},
				"attempts": {"type": "int"},
				"last_attempt": {"type": "datetime"},
				"last_login": {"type": "datetime"},
				"registered_time": {"type": "datetime"},
				"last_password_change": {"type": "datetime"}
			}');

		foreach ($spec as $key => $value){
			$properties->$key = $value;
		}
	}

	static function on_data(&$data)
	{
		// keep sensitive data from showing up on basic GET requests
		// TODO: not here, from data source!!
	}


/*
	static function registerAction($data){
		$user = static::$instance->register($data->email, $data->password);
		// send welcome+verification email
		return (object) array('id' => $user->id);
	}

	static function loginAction($data){
		$result = static::$instance->login($data->email, $data->password);
		return (object) $result;
	}

	static function verifyAction($data){
		$result = static::$instance->verify($data->email, $data->code);
		return (object) array('verified' => $result);
	}

	static function renewVerificationAction($data){
		$verification_code = static::$instance->renewVerification($data->email);
		// TODO: throttle this to avoid mass email hack
		// send mail with $verification_code to $data->email
		return (object) array('renewed' => true);
	}

	static function logoutAction($data){
		static::$instance->logout();
		return (object) array('logged' => false);
	}

	static function meAction($data){
		$current = static::$instance->current;
		if ($current){
			return $current;
		}
		return array();
	}

	static function changePasswordAction($data){
		static::$instance->changePassword($data->oldPassword, $data->password);
		// TODO: optionally send mail notifying password change
		return (object) array('changed' => true);
	}

	static function requestPasswordResetAction($data){
		$reset_code = static::$instance->requestPasswordReset($data->email);
		// TODO: send mail with reset code
		return (object) array('sent' => true);
	}

	static function resetPasswordAction($data){
		$result = static::$instance->resetPassword();
		// TODO: send mail with reset code
		return (object) array('reset' => true);
	}

*/




	public function getRequestSignature($request, $private_key)
	{
		$params = $this->request->data();
		if (isset($params['sig'])){
			unset($params['sig']);
		}

		ksort($params);

		$query = 
			strtoupper($request->method()) . ' ' .
			CURRENT_URL . '?' .
			http_build_query($params, '', '&', PHP_QUERY_RFC3986);

/*var_dump('str php: ' . $query);
echo "\n";
var_dump('b64 php: ' . base64_encode(hash_hmac("sha256", $query, $private_key, true)));
echo "\n";
var_dump('sig php: ' . rawurlencode(base64_encode(hash_hmac("sha256", $query, $private_key, true))));*/

		return rawurlencode(base64_encode(hash_hmac("sha256", $query, $private_key, true)));
	}


	function getUserByPublicKey($publicKey)
	{
		$query = "SELECT * FROM ".$this->config['user_table']." WHERE public_key = :public_key LIMIT 1;";
		$statement = $this->database->prepare($query);
		$statement->execute(array('public_key' => $publicKey));
		$data = $statement->fetchObject();
		if ($data){
			return $data;
		}
		return null;
	}

	function getUserById($id){
		$query = "SELECT * FROM ".$this->config['user_table']." WHERE id = :id LIMIT 1;";
		$statement = $this->database->prepare($query);
		$statement->execute(array('id' => $id));
		$data = $statement->fetchObject();
		if ($data){
			return $data;
		}
		return null;
	}

	function getUserByEmail($email){
		$query = "SELECT * FROM ".$this->config['user_table']." WHERE email = :email LIMIT 1;";
		$statement = $this->database->prepare($query);
		$statement->execute(array('email' => $email));
		$data = $statement->fetchObject();
		if ($data){
			return $data;
		}
		return null;
	}

	function createUser($user){
		$user = (array)$user;
		unset($user['id']);
		$fields = array_keys($user);
		$query = "INSERT INTO ".$this->config['user_table']." (".implode(',', $fields).") VALUES (:".implode(', :', $fields).")";
		$statement = $this->database->prepare($query);
		$result = $statement->execute($user);
		$id = $this->database->lastInsertId();
		if ($id){
			return $id;
		}
		return false;
	}

	function updateUser($user){
		$user = (array)$user;
		$fields = array_keys($user);
		$pairs = array();
		foreach ($fields as $field){
			if ($field != 'id'){
				$pairs[] = $field . " = :" . $field;
			}
		}
		$query = "UPDATE ".$this->config['user_table']." SET ".implode(', ', $pairs)." WHERE id = :id";

		$statement = $this->database->prepare($query);
		$result = $statement->execute($user);
		if ($result){
			return true;
		}
		return false;
	}

	private function generatePublicKey()
	{
		return base64_encode(md5(date('r').mt_rand(11111,99999)));
	}

	private function generatePrivateKey()
	{
		return base64_encode(md5(date('r').mt_rand(11111,99999)));
	}

	public function register($email, $password, $data = array())
	{
		$errors = array();

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ $errors[] = "User.email.incorrectType.email"; }
		if (strlen($email) > 50){ $errors[] = "User.email.tooLong"; }
		if (strlen($email) < 5){ $errors[] = "User.email.tooShort"; }

		if (strlen($password) < 6){ $errors[] = "User.password.tooShort"; }

		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		if ($this->isLogged()){
			throw new \UnauthorizedException(array('error' => 'User.registration.isLogged'));
		}

		/*// TODO: is this needed? db validates this also...
		$user = $this->getUserByEmail($email);
		if ($user){
			throw new \InvalidInputDataException(array("User.exists"));
		}else{
			$user = new \stdClass();
		}*/
		$user = new \stdClass();

		foreach ($data as $key => $value){
			$user->$key = $value;
		}

		// update to correct password value
		$user->email = $email;
		$user->password = password_hash($password, PASSWORD_BCRYPT, array()); // TODO: include global salt
		$user->registered_time = (new \DateTime('now'))->format('Y-m-d H:i:s');
		$user->verification_expiration = (new \DateTime('now'))
											->add(new \DateInterval($this->config['verification_lifespan']))
											->format('Y-m-d H:i:s');
		$user->verification_code = md5($email.date('r').mt_rand(11111,99999)); // TODO: include global salt

		// Keys are persistent across devices/clients
		// keys are ONLY used to identify a user via non html methods
		// and behave as a password
		$user->public_key = $this->generatePublicKey();
		$user->private_key = $this->generatePrivateKey();

		try{
			$id = $this->createUser($user);
			return $this->getUserById($id);
		}catch(\Exception $e){
			$id = false;
			if ($e->getCode() == 23000){
				throw new \InvalidInputDataException(array("User.registration.exists"));
			}else{
				throw $e;
			}
		}
	}

	public function renewVerification($email)
	{
		$user = $this->getUserByEmail($email);

		if ($user){
			if (!$this->isVerified($user)){
				$user->verification_expiration = (new \DateTime('now'))
													->add(new \DateInterval($this->config['verification_lifespan']))
													->format('Y-m-d H:i:s');
				$user->verification_code = md5($user->email.date('r').mt_rand(11111,99999)); // TODO: include global salt

				$result = $this->updateUser($user);
				if ($result){
					return $user->verification_code;
				}else{
					throw new \UnauthorizedException(array('error' => 'User.renewVerification.failed'));
				}
			}
			throw new \UnauthorizedException(array('error' => 'User.renewVerification.userIsVerified'));
		}
		throw new \UnauthorizedException(array('error' => 'User.renewVerification.notFound'));
	}

	public function verify($email, $verificationCode)
	{
		$user = $this->getUserByEmail($email);

		if ($user){
			if (!$this->isVerified($user) && $this->isActive($user)){
				$now = new \DateTime('now');
				if (new \DateTime($user->verification_expiration) >= $now){
					if ($user->verification_code == $verificationCode){
						$user->verification_code = '';
						$user->verified = 1;
						$user->verification_expiration = null;
						$user->verification_time = $now->format('Y-m-d H:i:s');
						$result = $this->updateUser($user);

						if (!$result){
							throw new \UnauthorizedException(array('error' => 'User.verification.failed'));
						}
					}else{
						throw new \UnauthorizedException(array('error' => 'User.verification.invalidCode'));
					}
				}else{
					throw new \UnauthorizedException(array('error' => 'User.verification.expiredCode'));
				}
			}
			return true;
		}
		throw new \UnauthorizedException(array('error' => 'User.verification.notFound'));
	}

	public function login($email, $password)
	{

		if ($this->isLogged()){
			return array(
				'id' => $this->current->id,
				'public_key' => $this->current->public_key,
				'private_key' => $this->current->private_key
			);
		}

		$errors = array();

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ $errors[] = "User.email.incorrectType.email"; }
		if (strlen($email) > 50){ $errors[] = "User.email.tooLong"; }
		if (strlen($email) < 5){ $errors[] = "User.email.tooShort"; }

		if (strlen($password) < 6){ $errors[] = "User.password.tooShort"; }

		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		$user = $this->getUserByEmail($email);

		if ($user && $this->isActive($user)){
			$now = new \DateTime('now');

			$threshold = (new \DateTime($user->last_attempt))->add(new \DateInterval('PT'.($user->attempts * 2).'S'));
			if ($threshold > $now){
				// deny auth attempt, frequency is too high for failed logins
				throw new \UnauthorizedException(array('error' => 'User.login.denied'));
			}

			if (password_verify($password, $user->password)){
				if ($this->config['force_account_verification'] && !$this->isVerified($user)){
					// user is not verified and settings force verification
					throw new \UnauthorizedException(array('error' => 'User.login.unverified'));
				}

				$user->attempts = 0;
				$user->last_login = (new \DateTime('now'))->format('Y-m-d H:i:s');
				$user->reset_expiration = '0000-00-00 00:00:00';
				$user->reset_code = NULL;

				// make sure user has keys, this regenerates keys
				// in case no keys are present
				if (!$user->public_key){
					$user->public_key = $this->generatePublicKey();
					$user->private_key = $this->generatePrivateKey();
				}

				$this->updateUser($user);

				$this->current = $user;
				$_SESSION['user.id'] = $this->current->id;
				$_SESSION['user.logged'] = true;

				return array(
					'id' => $user->id,
					'public_key' => $user->public_key,
					'private_key' => $user->private_key
				);
			}

			$user->attempts++;
			$user->last_attempt = $now->format('Y-m-d H:i:s');
			$this->updateUser($user);

			// TODO: add captcha eventually?

			throw new \UnauthorizedException(array('error' => 'User.login.wrongPassword'));
		}

		throw new \UnauthorizedException(array('error' => 'User.login.notFound'));
	}

	public function isLogged()
	{
		return ($this->current && $this->current->id > 0);
	}

	public function logout()
	{
		/*if ($this->isLogged() && $this->request->format() != 'html'){
			// login out via non-html methods will destroy keys, effectively logging out ALL clients that use those keys
			// * Sessions dont exist here
			$this->current->public_key = '';
			$this->current->private_key = '';
		}*/

		//$this->updateUser($this->current);

		$this->current = null;
		$_SESSION['user.logged'] = false;
		unset($_SESSION['user.id']);
		return true;
	}

	public function changePassword($oldPassword, $newPassword)
	{
		if (!$this->isLogged()){
			throw new \UnauthorizedException(array('error' => 'User.changePassword.unauthorized'));
		}

		if (!password_verify($oldPassword, $this->current->password)){
			throw new \UnauthorizedException(array('error' => 'User.changePassword.wrongPassword'));
		}

		// update to correct password value
		$this->current->password = password_hash($newPassword, PASSWORD_BCRYPT, array()); // TODO: include global salt
		$this->current->last_password_change = (new \DateTime('now'))->format('Y-m-d H:i:s');

		// Keys behave as passwords, change your password and keys will be 
		// regenerated
		$this->current->public_key = $this->generatePublicKey();
		$this->current->private_key = $this->generatePrivateKey();

		$result = $this->updateUser($this->current);

		if (!$result){
			throw new \UnauthorizedException(array('error' => 'User.changePassword.failed'));
		}
		return true;
	}

	public function requestPasswordReset($email)
	{
		if ($this->isLogged()){
			throw new \UnauthorizedException(array('error' => 'User.requestPasswordReset.isLogged'));
		}

		$errors = array();

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ $errors[] = "User.email.incorrectType.email"; }

		if (count($errors)) {
			throw new \InvalidInputDataException($errors);
		}

		$user = $this->getUserByEmail($email);

		if ($user && $this->isActive($user)){
			$reset_code = md5($user->email.date('r').mt_rand(11111,99999));
			$user->reset_code = password_hash($reset_code, PASSWORD_BCRYPT, array());
			$user->reset_expiration = (new \DateTime('now'))
											->add(new \DateInterval($this->config['reset_password_lifespan']))
											->format('Y-m-d H:i:s');
			$this->updateUser($user);

			return $reset_code;
		}

		throw new \UnauthorizedException(array('error' => 'User.requestPasswordReset.notFound'));
	}

	public function resetPassword($email, $reset_code, $password)
	{
		if ($this->isLogged()){
			throw new \UnauthorizedException(array('error' => 'User.resetPassword.isLogged'));
		}

		if ($this->canResetPassword($email, $reset_code)){

			$errors = array();

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)){ $errors[] = "User.email.incorrectType.email"; }
			if (strlen($email) > 50){ $errors[] = "User.email.tooLong"; }
			if (strlen($email) < 5){ $errors[] = "User.email.tooShort"; }

			if (strlen($password) < 6){ $errors[] = "User.password.tooShort"; }
			// TODO: DRY up these validations

			if (count($errors)) {
				throw new \InvalidInputDataException($errors);
			}

			$user = $this->getUserByEmail($email);
			if ($user && $this->isActive($user)){
				$user->password = password_hash($password, PASSWORD_BCRYPT, array());
				// Keys behave as passwords, change your password and keys will be 
				// regenerated
				$user->public_key = $this->generatePublicKey();
				$user->private_key = $this->generatePrivateKey();

				$this->updateUser($user);
				return true;
			}
			throw new \UnauthorizedException(array('error' => 'User.resetPassword.notFound'));
		}
		throw new \UnauthorizedException(array('error' => 'User.resetPassword.invalidCode'));
	}

	public function canResetPassword($email, $reset_code)
	{
		$user = $this->getUserById($email);

		if ($user && $this->isActive($user)){
			if (new \DateTime($user->reset_expiration) > new \DateTime('now')){
				if (password_verify($reset_code, $user->reset_code)){
					return true;
				}
			}
		}
		return false;
	}

	public function exists($user = false)
	{
		if ($user){
			return !empty($user->id);
		}else if($this->current){
			return $this->exists($this->current);
		}
		return false;
	}

	public function isVerified($user = false)
	{
		if ($user){
			return $user->verified == 1;
		}else if($this->current){
			return $this->isVerified($this->current);
		}
		return false;
	}

	public function isActive($user = false)
	{
		if ($user){
			return $user->active == 1;
		}else if($this->current){
			return $this->isActive($this->current);
		}
		return false;
	}

	public function __get($property)
	{
		if (isset($this->current->$property)){
			return $this->current->$property;
		}else{
			return null;
		}
	}

	public function __set($property, $value)
	{
		if ($this->current && property_exists($this->current, $property)){
			$this->current->$property = $value;
		}else{
			$this->$property = $value;
		}
	}

	public function __isset($property)
	{
		return isset($this->current->$property);
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