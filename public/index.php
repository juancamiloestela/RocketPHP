<?php 

$config = include '../config.php';
include '../launch.php';

echo $site->launch($request->uri(), $request->method(), $request->data());