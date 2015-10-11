<h1>Hi $user.name!</h1>
<p>
	Welcome to the Rocket team! Before you can access your account we need to verify your email. To do this simply click on the following link or copy and paste it in the browser.
	<a href="$site_url/account/verify?id=$user.id&code=$user.verification_code">
		$site_url/account/verify?id=$user.id&code=$user.verification_code
	</a>
</p>
<p>Sincerely,<br><strong>The Rocket Team</strong></p>