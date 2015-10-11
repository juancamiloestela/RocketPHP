<?php 
return array(
	"owns" => array(array("User", "is_logged"),array("Contexts", "is_owner"),array("Contexts", "is_admin")),
	"logged" => array(array("User", "is_logged")),
	"public" => array()
);