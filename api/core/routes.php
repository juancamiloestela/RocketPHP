<?php return array(
"\/patients" => array("class" => "Patient", "method" => "_patients", "args" => array("")),
"\/doctor" => array("class" => "Doctor", "method" => "_doctor", "args" => array("")),
"\/cars" => array("class" => "Job", "method" => "_cars", "args" => array("")),
"\/motorcycles" => array("class" => "Motorcycle", "method" => "_motorcycles", "args" => array("")),
"\/patients\/(?P<id>[^\/]+)\/path\/(?P<code>[^\/]+)" => array("class" => "Patient", "method" => "_patients_id_path_code", "args" => array("id", "code"))
);