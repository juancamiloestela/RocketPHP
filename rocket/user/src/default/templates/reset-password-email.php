<h1>Hi $user.name</h1>
<p>
	We have recieved a request to reset your password. If you did request this please click on the link below or copy and paste it on the browser. If you didn't request this you can ignore this email.
	<a href="$site_url/account/password-reset?id=$user.id&code=$user.reset_code">
		$site_url/account/password-reset?id=$user.id&code=$user.reset_code
	</a>
</p>
<p>Sincerely,<br><strong>The Rocket Team</strong></p>