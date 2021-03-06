<?php
namespace Rocket\Site;

class Generator{

	protected $system;
	protected $specs;
	protected $routes;

	public function __construct($system)
	{
		$this->system = $system;

		$this->loadSpecs();
		$this->generateContexts();
		$this->generatePages();
		$this->generateRoutes();
	}

	public function loadSpecs()
	{
		//echo 'loading specs'.PHP_EOL;
		$specFile = $this->system->config['payload'] . $this->system->config['spec_file'];

		$path = pathinfo($specFile, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;

		$specContent = file_get_contents($specFile);

		if (preg_match_all('/"include (.+)"/', $specContent, $matches, PREG_SET_ORDER)){
			foreach ($matches as $match){
				$specContent = str_replace($match[0], file_get_contents($path . $match[1]), $specContent);
			}
		}

		$this->specs = json_decode($specContent);
		if (!$this->specs){
			throw new \Exception('Invalid spec format');
		}
	}

	public function generateContexts()
	{
		//echo 'generating contexts'.PHP_EOL;
		ob_start();
		echo "return array(" . PHP_EOL;
		$contexts = array();
		$siteContexts = (array)$this->specs->contexts;
		uasort($siteContexts, function($a, $b){
			return (count($a) < count($b));
		});
		foreach ($siteContexts as $contextName => $context){
			$checks = array();
			foreach ($context as $check){
				if (strpos($check, '.')){
					$c = explode('.', $check);
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

		file_put_contents($this->system->config['core_path'] . 'contexts.php', '<?php ' . PHP_EOL . $src);
	}

	public function generatePages()
	{
		$this->routes = array();
		$parentTraits = array();

		if (isset($this->specs->traits)){
			$parentTraits = array_merge($parentTraits, $this->specs->traits);
		}

		if (isset($page->traits)){
			$parentTraits = array_merge($parentTraits, $page->traits);
			foreach ($page->traits as $traitName){
				if (method_exists($traitName, 'on_properties')){
					\Rocket::call(array($traitName, "on_properties"), $page->properties);
				}
			}
		}

		foreach ($this->specs->pages as $route => $page){
			//echo 'generating pages: '.$pageName . PHP_EOL;

			ob_start();

			$pageName = ucfirst(str_replace(array('/', '{', '}'), array('_', '', ''), trim($route, ' /')));

			echo "/**" . PHP_EOL;
			echo " * This class has been autogenerated by RocketPHP" . PHP_EOL;
			echo " */" . PHP_EOL . PHP_EOL;
			echo "namespace Pages;" . PHP_EOL . PHP_EOL;
			echo "class $pageName extends \Rocket\Site\Page{" . PHP_EOL . PHP_EOL;



				preg_match_all('/\{([^\}]+)\}/', $route, $matches, PREG_SET_ORDER);
				//print_r($matches);
				$args = array('$data');
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

				foreach ($page as $contextName => $context){
					if ($contextName == 'traits'){
						$parentTraits = array_merge($parentTraits, $page->traits);
						continue;
					}
					$contextChecks = $this->specs->contexts->$contextName;

					foreach ($context as $methodName => $method){
						if ($methodName == 'traits'){
							$parentTraits = array_merge($parentTraits, $context->traits);
							continue;
						}
						$methodName = strtoupper($methodName);

						if (!isset($method->traits)){
							$method->traits = array();
						}
						$method->traits = array_unique(array_merge($parentTraits, $method->traits));

						echo "\t". "function $methodName" . "_when_$contextName(" . implode(', ', $args) . ") {" . PHP_EOL;
						$this->routes[$routePattern] = "array(\"class\" => \"$pageName\", \"method\" => \"" . str_replace(array('/', '-'), '_', $routeName) . "\", \"args\" => array(\"".implode('", "', $argNames)."\"))";

						//echo "\t\t" . "\$data = array();" . PHP_EOL;
						echo "\t\t" . "\$errors = \$this->errors;" . PHP_EOL . PHP_EOL;

						$echoed = false;
						foreach ($args as $argName){
							if ($argName != '$data'){
								$echoed = true;
								echo "\t\t" . "\$data->".trim($argName, '$')." = $argName;" . PHP_EOL;
							}
						}
						if ($echoed){
							echo PHP_EOL;
						}

						if (isset($method->traits)){
							foreach ($method->traits as $trait){
								if (method_exists($trait, 'on_start')){
									echo "\t\t" . "\Rocket::call(array(\"$trait\", \"on_start\"), \$data);" . PHP_EOL;
								}
							}
						}

						if (isset($method->delegate)){
							$delegate = explode('.', $method->delegate);
							echo "\t\t" . "return \Rocket::call(array(\"$delegate[0]\", \"$delegate[1]\"), \$data);" . PHP_EOL;
						}else{
							if (isset($method->expects)){
								echo "\t\t" . "// check for required input data" . PHP_EOL;
								foreach ($method->expects as $expectedName){
									if (isset($page->properties->$expectedName)){
										$expected = $page->properties->$expectedName;
										echo "\t\t" . "if (!isset(\$data->$expectedName)){ \$errors[] = \"$pageName.$expectedName.required\"; }" . PHP_EOL;
										echo "\t\t" . "else{ \$data->$expectedName = \$this->receive_$expectedName(\$data->$expectedName, \$errors); }" . PHP_EOL;
									}else{
										// allow making virtual (non page data) inputs required
										echo "\t\t" . "if (!isset(\$data->$expectedName)){ \$errors[] = \"$pageName.$expectedName.required\"; }" . PHP_EOL;
									}
								}
								echo PHP_EOL;
							}

							if (isset($method->accepts)){
								echo "\t\t" . "// check optional input data if present" . PHP_EOL;
								foreach ($method->accepts as $acceptedName){
									echo "\t\t" . "if (isset(\$data->$acceptedName)){ \$data->$acceptedName = \$this->receive_$acceptedName(\$data->$acceptedName, \$errors); }" . PHP_EOL;
								}
								echo PHP_EOL;
							}

							//echo "\t\t" . 'if (count($errors)) {' . PHP_EOL;
							if (isset($method->traits)){
								foreach ($method->traits as $trait){
									if (method_exists($trait, 'on_error')){
										echo "\t" . "\Rocket::call(array(\"$trait\", \"on_error\"), \$data, \$errors);" . PHP_EOL;
									}
								}
							}
							// TODO: include below logic into trait logic above "control exception trigger"
							if (isset($method->on_error)){
								$on_error = explode('.', $method->on_error);
								echo "\t\t" . "if (\Rocket::call(array(\"$on_error[0]\", \"$on_error[1]\"), \$data, \$errors)){" . PHP_EOL;
								//echo "\t\t\t\t" . "throw new \InvalidInputDataException(\$errors);" . PHP_EOL;
								echo "\t\t\t" . "\$data->errors = \$errors;" . PHP_EOL;
								echo "\t\t" . "}" . PHP_EOL;
							}else{
								//echo "\t\t\t" . "throw new \InvalidInputDataException(\$errors);" . PHP_EOL;
								echo "\t\t" . "\$data->errors = \$errors;" . PHP_EOL;
							}
							//echo "\t\t" . '}' . PHP_EOL . PHP_EOL;

							/*$returnType = 'object';
							$schema = $method->returns;
							if (is_array($method->returns)){
								$returnType = 'collection';
								$schema = $method->returns[0];
							}else if (is_string($method->returns)){
								$returnType = 'relation';
								$schema = array();
							}*/

							if (isset($method->traits)){
								foreach ($method->traits as $trait){
									if (method_exists($trait, 'on_input')){
										echo "\t\t" . "\Rocket::call(array(\"$trait\", \"on_input\"), \$data);" . PHP_EOL;
									}
								}
							}
							if (isset($method->on_input)){
								$on_input = explode('.', $method->on_input);
								echo "\t\t" . "\Rocket::call(array(\"$on_input[0]\", \"$on_input[1]\"), \$data);" . PHP_EOL;
							}

							if (isset($method->on_action)){
								$on_action = explode('.', $method->on_action);
								echo "\t\t" . "\$data = \Rocket::call(array(\"$on_action[0]\", \"$on_action[1]\"), \$data);" . PHP_EOL;
							}else{
								if ($methodName == "GET"){
									if (!isset($method->template)){
										throw new \Exception('Site page must have a template specified');
									}
									if (isset($method->data)){
					//echo "\t\t" . "echo '<pre>';print_r(\$data);";
										foreach ($method->data as $varName => $dataSource){
											$dataSource = explode('.', $dataSource);
											preg_match('/\([^\)]*\)/i', $dataSource[1], $matches);

											//if (count($matches)){
												$dataSource[1] = str_replace($matches[0], '', $dataSource[1]);

												$dataSourceArgs = explode(',', trim($matches[0], '()'));
												$dataSourceArgValues = array();
												foreach ($dataSourceArgs as $dataSourceArgIndex => $dataSourceArgValue){
													$dataSourceArgValue = str_replace("'", '"', trim($dataSourceArgValue));
													if (preg_match_all('/\{([^\}]+)\}/', $dataSourceArgValue, $matches, PREG_SET_ORDER)){
														foreach ($matches as $match){
															//print_r($match);
															$dataSourceArgValue = str_replace($match[0], '$data->'.str_replace('.', '->', $match[1]), $dataSourceArgValue);
														}
													}
													$dataSourceArgValues[] = $dataSourceArgValue;
													//echo "\t\t" . "\$ref_$dataSourceArgIndex = $dataSourceArgValue;" . PHP_EOL;
												}
												echo "\t\t" . "\$args = array(".implode(', ', $dataSourceArgValues).");" . PHP_EOL;
												echo "\t\t" . "\$data->$varName = \Rocket::callArray(array(\"$dataSource[0]\", \"$dataSource[1]\"), \$args);" . PHP_EOL;
												//echo "\t\t" . "\$data->$varName = \Rocket::call(array(\"$dataSource[0]\", \"$dataSource[1]\"), \$ref_".implode(', $ref_', array_keys($dataSourceArgs)).");" . PHP_EOL;
											//}else{
											//	echo "\t\t" . "\$data->$varName = \Rocket::call(array(\"$dataSource[0]\", \"$dataSource[1]\"));" . PHP_EOL;
											//}

										}
									}
								}else if ($methodName == "POST"){
									if ($method->actions && count($method->actions)){
										echo "\t\t" . "try{" . PHP_EOL;
										foreach ($method->actions as $varName => $action){
											$action = explode('.', $action);
											preg_match('/\([^\)]*\)/i', $action[1], $matches);

											//if (count($matches)){
												$action[1] = str_replace($matches[0], '', $action[1]);

												$actionArgs = explode(',', trim($matches[0], '()'));
												$actionArgValues = array();

												foreach ($actionArgs as $actionArgIndex => $actionArgValue){
													$actionArgValue = str_replace("'", '"', trim($actionArgValue));
													if (preg_match_all('/\{([^\}]+)\}/', $actionArgValue, $matches, PREG_SET_ORDER)){
														foreach ($matches as $match){
															//print_r($match);
															$actionArgValue = str_replace($match[0], '$data->'.str_replace('.', '->', $match[1]), $actionArgValue);
														}
													}
													$actionArgValues[] = $actionArgValue;
												}
												//echo "\t\t\t" . "\$data->$varName = \Rocket::call(array(\"$action[0]\", \"$action[1]\"), \$ref_".implode(', $ref_', array_keys($actionArgs)).");" . PHP_EOL;
												echo "\t\t\t" . "\$args = array(".implode(', ', $actionArgValues).");" . PHP_EOL;
												echo "\t\t\t" . "\$data->$varName = \Rocket::callArray(array(\"$action[0]\", \"$action[1]\"), \$args);" . PHP_EOL;
											//}else{
											//	echo "\t\t\t" . "\$data->$varName = \Rocket::call(array(\"$action[0]\", \"$action[1]\"));" . PHP_EOL;
											//}
										}
										echo "\t\t" . "}catch (\InvalidInputDataException \$e){" . PHP_EOL;
										echo "\t\t\t" . "\$this->errors = \$e->errors();" . PHP_EOL;
										echo "\t\t\t" . "return \$this->system->launch('$route', 'GET', \$data);" . PHP_EOL;
										echo "\t\t" . "}" . PHP_EOL;
									}
								}else if ($methodName == "PUT"){
									
								}else if ($methodName == "DELETE"){
									
								}
							}

							if (isset($method->traits)){
								foreach ($method->traits as $trait){
									if (method_exists($trait, 'on_data')){
										echo "\t\t" . "\Rocket::call(array(\"$trait\", \"on_data\"), \$data);" . PHP_EOL;
									}
								}
							}
							if (isset($method->on_data)){
								$on_data = explode('.', $method->on_data);
								echo "\t\t" . "\Rocket::call(array(\"$on_data[0]\", \"$on_data[1]\"), \$data);" . PHP_EOL;
							}

							if ($methodName == "GET"){
							echo "\t\t" . "return \$this->template->render('$method->template', \$data);" . PHP_EOL;
							}else{
							echo "\t\t" . "//header('Location: $method->redirect');" . PHP_EOL;
							echo "\t\t" . "die('redirect');" . PHP_EOL;
							}
						}
						echo "\t}" . PHP_EOL . PHP_EOL;
					}
				}

			echo "}";

			$src = ob_get_contents();
			ob_end_clean();

			if (!file_exists($this->system->config['core_path'] . 'pages/')){
				mkdir($this->system->config['core_path'] . 'pages/', 0755, true);
			}
			file_put_contents($this->system->config['core_path'] . 'pages' . DIRECTORY_SEPARATOR . $pageName . '.php', '<?php ' . PHP_EOL . $src);
		}
	}


	public function generateRoutes()
	{
		//echo 'generating routes'.PHP_EOL;
		$routesWithoutPlaceholder = array();
		$routesWithPlaceholder = array();
		$routes = array();

		foreach ($this->routes as $route => $method){
			if (stripos($route, '(?P<') !== false){
				$routesWithPlaceholder[] = '"'.$route.'" => '.$method;
			}else{
				$routesWithoutPlaceholder[] = '"'.$route.'" => '.$method;
			}
		}
		$routes = array_merge($routesWithoutPlaceholder, $routesWithPlaceholder);

		$routes = '<?php return array(' . PHP_EOL . implode(',' . PHP_EOL, $routes) . PHP_EOL . ');';
		file_put_contents($this->system->config['core_path'] . 'routes.php', $routes);
	}

}