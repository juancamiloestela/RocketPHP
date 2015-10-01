<?php return array(
"\/users" => array("class" => "User", "method" => "_users", "args" => array("")),
"\/blogs" => array("class" => "Blogs", "method" => "_blogs", "args" => array("")),
"\/users\/(?P<id>[^\/]+)" => array("class" => "User", "method" => "_users_id", "args" => array("id")),
"\/blogs\/(?P<id>[^\/]+)" => array("class" => "Blogs", "method" => "_blogs_id", "args" => array("id"))
);