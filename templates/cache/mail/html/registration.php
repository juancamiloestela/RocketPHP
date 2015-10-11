<h1>Hola <?php echo $this->data->name; ?>!</h1>
<p>
	Welcome to RocketPHP! We have recieved your registration but want to keep your data safe. To do this we need you to activate your account
	by clicking on the activate link. 
	<a href="<?php echo $this->data->APP_URL; ?>account/verify?email=<?php echo $this->data->email; ?>&code=<?php echo $this->data->verification_code; ?>" style="background-color:#ff6600;color:#fff;border-radius:3px;font-weight:bold;display:inline-block;padding:0.5em;text-decoration:none;">
		Activate!
	</a>
</p>
<p>Sincerely,<br><strong>The RocketPHP Team</strong></p>