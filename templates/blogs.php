<h1>Blog List</h1>

<ul>
<repeat foreach="$blogs->data" key="$i" value="$blog">
	<li><a href="blogs/$blog.id">$blog.name</a></li>
</repeat>
</ul>

<form action="?" method="POST">
	<if condition="count($errors)">
		<strong>Errors</strong>
		<ul>
		<repeat foreach="$errors" key="$i" value="$error">
			<li>$error</li>
		</repeat>
		</ul>
	</if>

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