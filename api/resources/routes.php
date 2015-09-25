<?php return array(
"\/patients" => array("class" => "Patient", "method" => "_patients", "args" => array("")),
"\/patients\/(?P<id>[^\/]+)\/path\/(?P<code>[^\/]+)" => array("class" => "Patient", "method" => "_patients_id_path_code", "args" => array("id", "code")),
"\/doctor" => array("class" => "Doctor", "method" => "_doctor", "args" => array(""))
);