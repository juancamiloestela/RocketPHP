<h1><?php echo $this->data->blog->data->name; ?></h1>

<?php $errors = isset($errors) ? $errors : array();$errors_count = -1;$errors_total = count($errors);foreach ($errors as $i => $error):$engine->data["i"] = $i; $engine->data["error"] = $error; ?>
	<?php echo $this->data->error; ?>
<?php endforeach; ?>

<pre>
<?php print_r($this->data); ?>
</pre>