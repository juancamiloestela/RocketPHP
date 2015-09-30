<?php return array(
"\/users" => array("class" => "User", "method" => "_users", "args" => array("")),
"\/users\/(?P<id>[^\/]+)" => array("class" => "User", "method" => "_users_id", "args" => array("id"))
);