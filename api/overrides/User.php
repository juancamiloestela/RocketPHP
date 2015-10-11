<?php

class User{

	static function is_logged(){
		return false;
	}

	static function on_input(&$data){
		if (1){
			$data = array('error' => 'unauthorized');
			throw new \UnauthorizedException($data);
		}
	}



	static function register_action($data, $user, $mail, $template){
		$user = $user->register($data->email, $data->password, array(
			'name' => $data->name
		));

		// send welcome+verification email
		$html = $template->render('mail/html/registration.php', $user);
		$message = $mail->make($html, strip_tags($html));
		$message->from('no-reply@rocketphp.com', 'RocketPHP Team');
		$message->subject('RocketPHP - Welcome!');
		$message->to($user->email, $user->name);
		$message->send();

		return (object) array('id' => $user->id);
	}

	static function login_action($data, $user){
		$result = $user->login($data->email, $data->password);
		return (object) $result;
	}

	static function verify_action($data, $user){
		$result = $user->verify($data->email, $data->code);
		return (object) array('verified' => $result);
	}

	static function renewVerification_action($data, $user){
		$verification_code = $user->renewVerification($data->email);
		// TODO: throttle this to avoid mass email hack
		// send mail with $verification_code to $data->email
		$html = $template->render('mail/html/verification.php', $user);
		$message = $mail->make($html, strip_tags($html));
		$message->from('no-reply@rocketphp.com', 'RocketPHP Team');
		$message->subject('RocketPHP - Welcome!');
		$message->to($user->email, $user->name);
		$message->send();

		return (object) array('renewed' => true);
	}

	static function logout_action($data, $user){
		$user->logout();
		return (object) array('logged' => false);
	}

	static function me_action($data, $user){
		$current = $user->current;
		if ($current){
			return $current;
		}
		return array();
	}

	static function changePassword_action($data, $user){
		$user->changePassword($data->oldPassword, $data->password);
		
		// TODO: optionally send mail notifying password change
		$html = $template->render('mail/html/password-change.php', $user);
		$message = $mail->make($html, strip_tags($html));
		$message->from('no-reply@rocketphp.com', 'RocketPHP Team');
		$message->subject('RocketPHP - Welcome!');
		$message->to($user->email, $user->name);
		$message->send();

		return (object) array('changed' => true);
	}

	static function requestPasswordReset_action($data, $user){
		$reset_code = $user->requestPasswordReset($data->email);
		
		// TODO: send mail with reset code
		$html = $template->render('mail/html/reset-password.php', $user);
		$message = $mail->make($html, strip_tags($html));
		$message->from('no-reply@rocketphp.com', 'RocketPHP Team');
		$message->subject('RocketPHP - Welcome!');
		$message->to($user->email, $user->name);
		$message->send();

		return (object) array('sent' => true);
	}

	static function resetPassword_action($data, $user){
		$result = $user->resetPassword();
		
		// TODO: send mail with reset code
		$html = $template->render('mail/html/password-change.php', $user);
		$message = $mail->make($html, strip_tags($html));
		$message->from('no-reply@rocketphp.com', 'RocketPHP Team');
		$message->subject('RocketPHP - Welcome!');
		$message->to($user->email, $user->name);
		$message->send();

		return (object) array('reset' => true);
	}
}