<?php 

$config = include '../../../config.php';
include '../../../launch.php';

echo $api->launch($request->uri(), $request->method(), $request->data());