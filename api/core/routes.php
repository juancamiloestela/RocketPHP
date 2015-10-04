<?php return array(
"\/users" => array("class" => "User", "method" => "_users", "args" => array("")),
"\/blogs" => array("class" => "Blogs", "method" => "_blogs", "args" => array("")),
"\/posts" => array("class" => "Posts", "method" => "_posts", "args" => array("")),
"\/tags" => array("class" => "Tags", "method" => "_tags", "args" => array("")),
"\/users\/(?P<id>[^\/]+)" => array("class" => "User", "method" => "_users_id", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)" => array("class" => "Blogs", "method" => "_blogs_id", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)\/posts" => array("class" => "Blogs", "method" => "_blogs_id_posts", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)\/owner" => array("class" => "Blogs", "method" => "_blogs_id_owner", "args" => array("id")),
"\/posts\/(?P<id>[^\/]+)" => array("class" => "Posts", "method" => "_posts_id", "args" => array("id")),
"\/posts\/(?P<id>[^\/]+)\/blog" => array("class" => "Posts", "method" => "_posts_id_blog", "args" => array("id")),
"\/posts\/(?P<id>[^\/]+)\/tags" => array("class" => "Posts", "method" => "_posts_id_tags", "args" => array("id")),
"\/posts\/tagged\/(?P<tag>[^\/]+)" => array("class" => "Posts", "method" => "_posts_tagged_tag", "args" => array("tag")),
"\/tags\/(?P<id>[^\/]+)" => array("class" => "Tags", "method" => "_tags_id", "args" => array("id")),
"\/tags\/(?P<id>[^\/]+)\/posts" => array("class" => "Tags", "method" => "_tags_id_posts", "args" => array("id")),
"\/tagged\/(?P<tag>[^\/]+)" => array("class" => "Tags", "method" => "_tagged_tag", "args" => array("tag")),
"\/tagged\/(?P<tag>[^\/]+)\/posts" => array("class" => "Tags", "method" => "_tagged_tag_posts", "args" => array("tag"))
);