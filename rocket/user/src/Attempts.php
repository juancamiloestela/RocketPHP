<?php
namespace Rocket\User;
/**
 * User System to handle users in RocketPHP.
 *
 * This system will handle user login and registration
 * in RocketPHP apps.
 *
 * @package    Rocket\User
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Initial implementation
 */

class Attempt
{
	/**
	 * Attempt success flag
	 * @var bool
	 */
	private $succeeded;

	/**
	 * Attempt status
	 * A code that further describes the
	 * attempt result
	 * @var string
	 */
	public $status;

	/**
	 * Attempt result data to return
	 * @var mixed
	 */
	protected $data;

	/**
	 * Attempt OK result status constant
	 */
	const OK = 'OK';

	/**
	 * Attempt FAILED result status constant
	 */
	const FAILED = 'FAILED';


	/**
	 * Attempt constructor
	 * @param bool $succeeded Attempt success
	 * @param string $status    Attempt descriptor
	 * @param array  $data      Attempt result data
	 */
	public function __construct($succeeded, $status, $data = array())
	{
		$this->succeeded = $succeeded;
		$this->status = $status;
		$this->data = $data;
	}

	/**
	 * Attempt fail test
	 * @return bool True if attempt failed
	 */
	public function failed()
	{
		return !$this->succeeded;
	}

	/**
	 * Attempt success test
	 * @return bool True if attempt succeeded
	 */
	public function succeeded()
	{
		return $this->succeeded;
	}

	/**
	 * Attempt result errors
	 * @return array Array with errors
	 */
	public function errors()
	{
		$data = array();
		if (!$this->succeeded){
			$data = $this->data;
		}
		return new \ArrayObject($data, \ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * Attempt data
	 * @return array Array with result data
	 */
	public function data($key = false)
	{
		if ($key !== false && isset($this->data[$key])){
			return $this->data[$key];
		}
		return new \ArrayObject($this->data, \ArrayObject::ARRAY_AS_PROPS);
	}
}

class RegistrationAttempt extends Attempt
{
	const INVALID_DATA = 'INVALID_DATA';
	const USER_EXISTS = 'USER_EXISTS';
}

class VerificationAttempt extends Attempt
{
	const INVALID_CODE = 'INVALID_CODE';
	const EXPIRED_CODE = 'EXPIRED_CODE';
}

class RenewVerificationAttempt extends Attempt
{
	const INVALID_DATA = 'INVALID_DATA';
	const USER_IS_VERIFIED = 'USER_IS_VERIFIED';
	const USER_DOES_NOT_EXIST = 'USER_DOES_NOT_EXIST';
}

class ResetPasswordRequestAttempt extends Attempt
{
	const INVALID_DATA = 'INVALID_DATA';
	const USER_DOES_NOT_EXIST = 'USER_DOES_NOT_EXIST';
}

class ResetPasswordAttempt extends Attempt
{
	const INVALID_DATA = 'INVALID_DATA';
}

class ChangePasswordAttempt extends Attempt
{
	const INVALID_DATA = 'INVALID_DATA';
	const UNAUTHORIZED = 'UNAUTHORIZED';
}

class LoginAttempt extends Attempt
{

	private $id;
	private $email;

	const INVALID_DATA = 'INVALID_DATA';
	const USER_NOT_VERIFIED = 'USER_NOT_VERIFIED';
	const USER_DOES_NOT_EXIST = 'USER_DOES_NOT_EXIST';
	const WRONG_PASSWORD = 'WRONG_PASSWORD';
	const DENIED = 'DENIED';

	function __construct($result, $code, $data = array())
	{
		if ($code == static::USER_NOT_VERIFIED ||
			$code == static::WRONG_PASSWORD ||
			$code == static::OK ||
			$code == static::DENIED){
			$this->id = $data;
		}else if ($code == static::USER_DOES_NOT_EXIST){
			$this->email = $data;
		}
		parent::__construct($result, $code, $data);
	}

	function email()
	{
		return $this->email;
	}

	function id()
	{
		return $this->id;
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