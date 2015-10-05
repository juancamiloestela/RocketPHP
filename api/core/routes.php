<?php return array(
"\/users" => array("class" => "User", "method" => "_users", "args" => array("")),
"\/blogs" => array("class" => "Blog", "method" => "_blogs", "args" => array("")),
"\/posts" => array("class" => "Post", "method" => "_posts", "args" => array("")),
"\/tags" => array("class" => "Tag", "method" => "_tags", "args" => array("")),
"\/users\/(?P<id>[^\/]+)" => array("class" => "User", "method" => "_users_id", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)" => array("class" => "Blog", "method" => "_blogs_id", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)\/posts" => array("class" => "Blog", "method" => "_blogs_id_posts", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)\/owner" => array("class" => "Blog", "method" => "_blogs_id_owner", "args" => array("id")),
"\/posts\/(?P<id>[^\/]+)" => array("class" => "Post", "method" => "_posts_id", "args" => array("id")),
"\/posts\/(?P<id>[^\/]+)\/blog" => array("class" => "Post", "method" => "_posts_id_blog", "args" => array("id")),
"\/posts\/(?P<id>[^\/]+)\/tags" => array("class" => "Post", "method" => "_posts_id_tags", "args" => array("id")),
"\/posts\/tagged\/(?P<tag>[^\/]+)" => array("class" => "Post", "method" => "_posts_tagged_tag", "args" => array("tag")),
"\/tags\/(?P<id>[^\/]+)" => array("class" => "Tag", "method" => "_tags_id", "args" => array("id")),
"\/tags\/(?P<id>[^\/]+)\/posts" => array("class" => "Tag", "method" => "_tags_id_posts", "args" => array("id")),
"\/tagged\/(?P<tag>[^\/]+)" => array("class" => "Tag", "method" => "_tagged_tag", "args" => array("tag")),
"\/tagged\/(?P<tag>[^\/]+)\/posts" => array("class" => "Tag", "method" => "_tagged_tag_posts", "args" => array("tag"))
);