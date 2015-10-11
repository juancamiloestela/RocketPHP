<?php
namespace Rocket\User;
/**
 * Short description
 *
 * Long description
 *
 * @package    Rocket\User
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Initial implementation
 */

class User implements \JsonSerializable
{
	public $id;
	public $email;
	public $name;
	public $password;
	public $public_key;
	public $private_key;
	public $role;
	public $active = 1;
	public $verified = 0;
	public $verification_code;
	public $verification_time;
	public $verification_expiration;
	public $reset_code;
	public $reset_expiration;
	public $attempts;
	public $last_attempt;
	public $last_login;
	public $registered_time;
	public $timezone;
	public $last_password_change;

	static $__metadata = array(
		'model_sql' => array(
			'password' => array(
				'type' => 'CHAR(60)'
			),
			'verification_code' => array(
				'type' => 'CHAR(60)'
			),
			'reset_code' => array(
				'type' => 'CHAR(60)'
			)
		),
		'validation_rules' => array(
			'email' => 'required|email|unique',
			'name' => 'required',
			'password' => 'required|length(7)',
			'password-confirmation' => 'virtual|required|confirms(password)',
			'old-password' => 'virtual|required'
		),
		'validation_contexts' => array(
			'login' => array('email' => 'required|email', 'password' => 'required'),
			'register' => array('email','name','password','password-confirmation'),
			'requestPasswordReset' => array('email' => 'required|email'),
			'resetPassword' => array('password', 'password-confirmation'),
			'changePassword' => array('password', 'password-confirmation', 'old-password')
		)
	);


	public function __construct()
	{

	}

	/*public function isVerified()
	{
		return $this->verified == 1;
	}*/

	/*public function isLogged()
	{
		die('USING isLogged on user instance!');
		return isset($_SESSION['logged']) ? $_SESSION['logged'] : false;
	}*/

	/*public function isActive()
	{
		return $this->active == 1;
	}*/

	/*public function exists()
	{
		return $this->id !== null;
	}*/

	/**
	 * Returns model properties and values as an
	 * associative array.
	 * @return array property/value array
	 */
	/*public function toArray()
	{
		$array = array();
		foreach ($this as $key => $value){
			$array[$key] = $value;
		}
		return $array;
	}*/

	public function hydrate($data)
	{
		foreach ($data as $key => $value){
			if (property_exists($this, $key)){
				$this->$key = $value;
			}
		}
	}

	/**
	 * Returns model properties and values as an
	 * associative array.
	 * @return array property/value array
	 */
	public function toArray($only = false)
	{
		$array = array();
		foreach ($this as $key => $value){
			// Ignore all properties that begin
			// with __ or rocket_ these are considered
			// internal model properties that should
			// not be propagated into databases or modified 
			// by the user
			if (!preg_match('/^(__|rocket_)/i', $key) && !($value instanceof Relation) || ($only && in_array($key, $only))){
				$array[$key] = $value;
			}else if ($value instanceof Relation){
				//echo get_class($value).' '.$value->isLoaded().'<br>';
				if ($value->isLoaded()){
					$array[$key] = $value->toArray();
				}
			}
		}
		unset($array['password']);
		unset($array['private_key']);
		unset($array['reset_code']);
		unset($array['verification_code']);
		return $array;
	}

	public function toUnsecureArray($only = false){
		$array = $this->toArray($only);
		$array['password'] = $this->password;
		$array['private_key'] = $this->private_key;
		$array['reset_code'] = $this->reset_code;
		$array['verification_code'] = $this->verification_code;
		return $array;
	}

	public function jsonSerialize() {
		return $this->toArray();
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