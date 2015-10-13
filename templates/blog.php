<h1>$blog->data->name</h1>

<repeat foreach="$errors" key="$i" value="$error">
	$error
</repeat>

<pre>
<?php print_r($this->data); ?>
</pre>