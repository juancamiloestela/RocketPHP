<h1>Blog List</h1>

<ul>
<?php $blogs->data = isset($blogs->data) ? $blogs->data : array();$blogsdata_count = -1;$blogsdata_total = count($blogs->data);foreach ($blogs->data as $i => $blog):$engine->data["i"] = $i; $engine->data["blog"] = $blog; ?>
	<li><a href="blogs/<?php echo $this->data->blog->id; ?>"><?php echo $this->data->blog->name; ?></a></li>
<?php endforeach; ?>
</ul>

<form action="?" method="POST">
	<?php if (count($errors)): ?>
		<strong>Errors</strong>
		<ul>
		<?php $errors = isset($errors) ? $errors : array();$errors_count = -1;$errors_total = count($errors);foreach ($errors as $i => $error):$engine->data["i"] = $i; $engine->data["error"] = $error; ?>
			<li><?php echo $this->data->error; ?></li>
		<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<label>
		Name <input type="text" name="name" value="" />
	</label>
	<label>
		Description <input type="text" name="description" value="" />
	</label>
	<button type="submit">Add</button>
</form>

<pre>
<?php //print_r($this->data); ?>
</pre>