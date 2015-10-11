<?php

if (!function_exists('password_hash')){

	/**
	 * Support for php 5.3.7- is given so this functions will
	 * eventually be replaced by php's
	 */

	if (CRYPT_BLOWFISH != 1){
		die('BCRYPT IS NOT SUPPORTED');
	}

	if (!defined('PASSWORD_BCRYPT')){
		define('PASSWORD_BCRYPT', 'PASSWORD_BCRYPT');
	}

	/**
	 * [password_hash description]
	 * @param  [type] $password  [description]
	 * @param  [type] $algorithm [description]
	 * @param  array  $options   [description]
	 * @return [type]            [description]
	 *
	 */
	function password_hash($password, $algorithm, $options = array()){
		$alphabet = '0123456789.AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz/';

		$cost = isset($options['cost']) ? $options['cost'] : 10;
		$cost = ($cost < 4) ? 4 : $cost;
		$cost = ($cost > 31) ? 31 : $cost;
		$cost = str_pad($cost, 2, '0', STR_PAD_LEFT);

		$type = 'y';

		$salt = '$2'.$type.'$'.$cost.'$';
		for ($i = 0; $i < 22; $i++){
			$salt .= $alphabet[rand(0,63)];
		}

		$hash = crypt($password, $salt);

		if (strlen($hash) > 13){
			return $hash;
		}
		return false;
	}

	function password_get_info($hash){
		$algorithm = 0;
		$algorithmName = 'unknown';
		$options = array();

		if (substr($hash, 0, 4) == '$2y$' && strlen($hash) == 60) {
			$algorithm = PASSWORD_BCRYPT;
			$algorithmName = 'bcrypt';
			preg_match('/^\$2y\$([0-9]+)\$/', $hash, $cost);
			$options['cost'] = $cost[1];
		}

		return array(
			'algo' => $algorithm,
			'algoName' => $algorithmName,
			'options' => $options,
		);
	}

	function password_needs_rehash($hash, $algorithm, $options = array()){
		$info = password_get_info($hash);
		if ($info['algo'] != $algorithm){
			return true;
		}
		switch ($algorithm){
			case PASSWORD_BCRYPT:
				$cost = isset($options['cost']) ? $options['cost'] : 10;
				if ($cost != $info['options']['cost']){
					return true;
				}
			break;
		}
		return false;
	}

	function password_verify($password, $hash){
		$hashed = crypt($password, $hash);
		return $hashed === $hash;
	}

}