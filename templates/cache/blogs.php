<h1>Blog List</h1>

<ul>
<?php $blogs->data = isset($blogs->data) ? $blogs->data : array();$blogsdata_count = -1;$blogsdata_total = count($blogs->data);foreach ($blogs->data as $i => $blog):$engine->data["i"] = $i; $engine->data["blog"] = $blog; ?>
	<li><a href="blogs/<?php echo $this->data->blog->id; ?>"><?php echo $this->data->blog->name; ?></a></li>
<?php endforeach; ?>
</ul>

<pre>
<?php print_r($this->data); ?>
</pre>