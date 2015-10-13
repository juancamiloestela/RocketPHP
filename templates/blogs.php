<h1>Blog List</h1>

<ul>
<repeat foreach="$blogs->data" key="$i" value="$blog">
	<li><a href="blogs/$blog.id">$blog.name</a></li>
</repeat>
</ul>

<pre>
<?php print_r($this->data); ?>
</pre>