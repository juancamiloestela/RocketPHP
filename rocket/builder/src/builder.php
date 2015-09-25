<?php
error_reporting(E_ALL);

$specFile = '../../../spec/spec.json';
$specContent = file_get_contents($specFile);

// handle all includes
if (preg_match_all('/"include (.+)"/', $specContent, $matches, PREG_SET_ORDER)){
	foreach ($matches as $match){
		$specContent = str_replace($match[0], file_get_contents('../../../'.$match[1]), $specContent);
	}
}

$api = json_decode($specContent);

$config = include '../../../api/config.php';

echo '<pre>';print_r($config);
die();

echo '<pre>';

ob_start();
echo "return array(" . PHP_EOL;
$contexts = array();
$apiContexts = (array)$api->contexts;
uasort($apiContexts, function($a, $b){
	return (count($a) < count($b));
});
foreach ($apiContexts as $contextName => $context){
	$checks = array();
	foreach ($context as $check){
		if (strpos($check, '::')){
			$c = explode('::', $check);
			$checks[] = "array(\"$c[0]\", \"$c[1]\")";
		}else{
			$checks[] = "array(false, \"$check\")";
		}
		//$checks[] = "\"$check\"";
	}
	$contexts[] = "\t" . "\"$contextName\" => array(" . implode(',', $checks) . ")";
}
echo implode(',' . PHP_EOL, $contexts);
echo PHP_EOL . ");";

$src = ob_get_contents();
ob_end_clean();
echo $src;
file_put_contents('contexts.php', '<?php ' . PHP_EOL . $src);

die();


foreach ($api->traits as $traitName => $trait){
	
}



$routes = array();
foreach ($api->resources as $resourceName => $resource){
	ob_start();

	if (is_string($resource) && preg_match('/^include (.+)/', $resource, $matches)){
		// TODO: make this include logic global, parse and replace src before decoding json
		//$resource = json_decode( file_get_contents($matches[1]) );
		//$api->resources->$resourceName = $resource;
	}

	echo "class $resourceName {" . PHP_EOL . PHP_EOL;

	echo "\t" . "protected \$db;" . PHP_EOL;

	echo "\t" . "function __construct(\$db){" . PHP_EOL;
	echo "\t\t" . "\$this->db = \$db;" . PHP_EOL;
	echo "\t" . "}" . PHP_EOL;

	// Render all validation and reciever methods
	foreach ($resource->properties as $propertyName => $property){
		echo "\t" . "function receive_$propertyName(\$value, &\$errors) {" . PHP_EOL;
		echo "\t\t" . "\$errors = array_merge(\$errors, \$this->validate_$propertyName(\$value));" . PHP_EOL;
		echo "\t\t" . "// TODO: \$value = customHook(\$value);" . PHP_EOL;
		echo "\t\t" . "return \$value;" . PHP_EOL;
		echo "\t}" . PHP_EOL . PHP_EOL;

		echo "\t" . "function validate_$propertyName(\$value) {" . PHP_EOL;
		echo "\t\t". "\$errors = array();" . PHP_EOL;
		switch ($property->type){
			case "string":
				echo "\t\t" . "if (!is_string(\$value)){ \$errors[] = \"$propertyName.incorrectType.string\"; }" . PHP_EOL;
				break;
			case "numeric":
				echo "\t\t" . "if (!is_numeric(\$value)){ \$errors[] = \"$propertyName.incorrectType.numeric\"; }" . PHP_EOL;
				break;
		}
		if (isset($property->max_length)){
			echo "\t\t" . "if (strlen(\$value) > $property->max_length){ \$errors[] = \"$propertyName.tooLong\"; }" . PHP_EOL;
		}
		if (isset($property->min_length)){
			echo "\t\t" . "if (strlen(\$value) < $property->min_length){ \$errors[] = \"$propertyName.tooShort\"; }" . PHP_EOL;
		}
		if (isset($property->matches)){
			echo "\t\t" . "if (!preg_match(\"$property->matches\", \$value)){ \$errors[] = \"$propertyName.patternMatch\"; }" . PHP_EOL;
		}
		echo "\t\t" . "return \$errors;" . PHP_EOL;
		echo "\t}" . PHP_EOL . PHP_EOL;
	}

	// Render all endpoints
	foreach ($resource->endpoints as $route => $endpoint){
		preg_match_all('/\{([^\}]+)\}/', $route, $matches, PREG_SET_ORDER);
		//print_r($matches);
		$args = array();
		$argNames = array();
		$routeName = $route;
		$routePattern = str_replace('/', '\/', $route);
		if (count($matches)){
			$routeName = str_replace(array('{', '}'), '', $route);
			foreach ($matches as $key => $value){
				$args[] = '$' . $value[1];
				$argNames[] = $value[1];
				$routePattern = str_replace('{'.$value[1].'}', '(?P<'.$value[1].'>[^\/]+)', $routePattern);
			}
		}

		foreach ($endpoint as $contextName => $context){
			$contextChecks = $api->contexts->$contextName;

			foreach ($context as $methodName => $method){
				$methodName = strtoupper($methodName);
				echo "\t". "function $methodName" . str_replace('/', '_', $routeName) . "_when_$contextName(" . implode(', ', $args) . ") {" . PHP_EOL;
				$routes[$routePattern] = "array(\"class\" => \"$resourceName\", \"method\" => \"" . str_replace('/', '_', $routeName) . "\", \"args\" => array(\"".implode('", "', $argNames)."\"))";

				echo "\t\t" . "\$data = array();" . PHP_EOL . PHP_EOL;
				echo "\t\t" . "\$errors = array();" . PHP_EOL . PHP_EOL;

				if (isset($method->queryParams)){
					echo "\t\t" . "// check query string data" . PHP_EOL;
					foreach ($method->queryParams as $paramName => $param){
						echo "\t\t" . "\$$paramName = \$this->receive_$paramName(\$_GET[\"$paramName\"], \$errors);" . PHP_EOL;
					}
					echo PHP_EOL;
				}

				if (isset($method->expects)){
					echo "\t\t" . "// check for required input data" . PHP_EOL;
					foreach ($method->expects as $expectedName){
						$expected = $resource->properties->$expectedName;
						echo "\t\t" . "if (!isset(\$_GET[\"$expectedName\"])){ \$errors[] = \"$expectedName.required\"; }" . PHP_EOL;
						echo "\t\t" . "else{ \$$expectedName = \$this->receive_$expectedName(\$_GET[\"$expectedName\"], \$errors); }" . PHP_EOL;
					}
					echo PHP_EOL;
				}

				if (isset($method->accepts)){
					echo "\t\t" . "// check optional input data if present" . PHP_EOL;
					foreach ($method->accepts as $acceptedName){
						echo "\t\t" . "if (isset(\$$acceptedName)){ \$$acceptedName = \$this->receive_$acceptedName(\$_REQUEST[\"$acceptedName\"], \$errors); }" . PHP_EOL;
					}
					echo PHP_EOL;
				}

				echo "\t\t" . 'if (count($errors) > 0) {' . PHP_EOL;
				echo "\t\t\t" . "throw new InvalidInputDataException(\$errors);" . PHP_EOL;
				echo "\t\t" . '} else {' . PHP_EOL;
				echo "\t\t\t" . '// TODO: $data = customHook($data);' . PHP_EOL;

				$returnType = 'object';
				$schema = $method->returns;
				if (is_array($method->returns)){
					$returnType = 'collection';
					$schema = $method->returns[0];
				}

				$requestedFields = array();
				foreach ($schema as $key => $value){
					$requestedFields[] = $key;
				}

				if ($methodName == "GET"){
					if ($returnType == 'object'){
						echo "\t\t\t" . "\$statement = \$this->db->prepare(\"select ".implode(',', $requestedFields)." from some_table where id = :id LIMIT 1;\");" . PHP_EOL;
						echo "\t\t\t" . "\$statement->execute(array('id' => \"1\"));" . PHP_EOL;
						echo "\t\t\t" . "\$data = \$statement->fetch(PDO::FETCH_ASSOC);" . PHP_EOL;
					}else if ($returnType == 'collection'){
						echo "\t\t\t" . "\$statement = \$this->db->prepare(\"select * from some_table;\");" . PHP_EOL;
						echo "\t\t\t" . "\$statement->execute();" . PHP_EOL;
						echo "\t\t\t" . "\$data = \$statement->fetchAll(PDO::FETCH_ASSOC);" . PHP_EOL;
					}
				}else if ($methodName == "POST"){

				}
				echo "\t\t" . '}' . PHP_EOL;

				echo "\t\t" . 'return $data;' . PHP_EOL;
				echo "\t}" . PHP_EOL . PHP_EOL;
			}
		}
	}

	echo "}";

	$src = ob_get_contents();
	ob_end_clean();

	if (!file_exists('resources/')){
		mkdir('resources/', 0755, true);
	}
	file_put_contents('resources/' . $resourceName . '.php', '<?php ' . PHP_EOL . $src);
}



$r = array();
foreach ($routes as $route => $method){
	$r[] = '"'.$route.'" => '.$method;
}
$routes = '<?php return array(' . PHP_EOL . implode(',' . PHP_EOL, $r) . PHP_EOL . ');';
file_put_contents('routes.php', $routes);

//echo $src;



include 'database-system/src/System.php';

$database = \Rocket\Container::set('database', new \Rocket\Database\System($config['database']) );

foreach ($api->resources as $resourceName => $resource){
	if (!in_array($resourceName, $existingTables)){
		$st = $db->prepare('CREATE TABLE `'.$resourceName.'` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;');
		$st->execute();
	}
	foreach ($resource->properties as $propertyName => $property){
		echo $resourceName.'::'.$propertyName.PHP_EOL;
	}
}



