<?php
function renderArrayData($label, $array){
	$html = '<div class="env-container">';
    $html .= '<h4>'.$label.'</h4>';
    $html .= '<div class="right-column">';
    if (count($array)){
        foreach ($array as $key => $value){
            $value = ($value === null) ? '<span class="null">null</span>' : $value;
            $value = ($value === false) ? '<span class="false">false</span>' : $value;
            $value = ($value === true) ? '<span class="true">true</span>' : $value;
            $value = (is_array($value)) ? renderArrayData('array', $value) : $value;

            $html .= '<div class="data-row">';
            $html .= '<span class="label">' . $key . ': </span><span class="value">' . $value . '</span>';
            $html .= '</div>';
        }
    }else{
        $html .= '<div class="data-row">';
        $html .= '<em class="value">Empty</em>';
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
?><!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Rocket Error</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
		<link href='https://fonts.googleapis.com/css?family=Raleway:400,700' rel='stylesheet' type='text/css'>
        <style>
            html{
                height:100%;
            }
            body{
                margin:0;
                padding:0;
                height:100%;
                color:#333;
                font-size:14px;
                font-family: "Raleway", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Geneva, Verdana, sans-serif;
            }
			h1{
				background-color: #e21111;
				color: #fff;
				margin:0;
				line-height: 2em;
				padding:0 0.5em;
			}
			h3{
				font-size: 2em;
			}
			.error{
				background-color: #bfbfbf;
				padding:1em;
				color:#4c4c4c;
			}
				.error h2{
					margin:0;
				}
			.code{
				background-color: #fff;
				border:1em solid #d9d9d9;
				padding:1em;
				font-family: monospace;
				color: #7d7d7d;
			}
				.code .highlight{
					background-color: #FFF1BE;
					font-weight: bold;
					color: #333;
				}
			.stack-trace{
				padding:1em;
			}
				.stack-trace ol{
					font-family: monospace;
                    border: 1px solid #EAEAEA;
				}
				.stack-trace ol li{
                    padding:0.5em 0;
				}
                    .stack-trace ol li .src{
                        cursor: pointer;
                    }
                    .stack-trace ol li + li{
                        border-top:1px dotted #dedede;
                    }
                    .stack-trace ol li .args{
                        display: none;
                    }
                        .stack-trace ol li.open .args{
                            display: block;
                        }
				.stack-trace .src{

				}
				.stack-trace .file{
					color:#b0b0b0;
				}
			.environment{
				padding:1em;
			}
				.env-container{
					overflow: hidden;
					padding: 1em;
					border-top: 1px solid #cacaca;
				}
					.env-container h4{
						float: left;
						width: 10%;
						min-width: 150px;
						margin: 0;
					}
					.env-container .right-column{
						float: left;
						margin-left: 1em;
					}
				.environment .data-row{
					font-family: monospace;
				}
					.environment .data-row .data-row{
						margin-left: 3em;
					}
				.environment .data-row .label{
					line-height: 1.6em;
					font-weight: bold;
				}
				.environment .data-row .value{
				}
					.environment .data-row em.value{
						color: #a7a7a7;
					}

				.partial-output pre{
					font-family: monospace;
					background-color: #FFF1BE;
					padding: 1em;
				}
            .toggle-arrow{
                display:inline-block;
                width:0;
                height:0;
                border-width: 0.5em;
                border-style: solid;
                border-color: transparent transparent transparent #cacaca;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
		<h1>Houston... we have a problem.</h1>

		<div class="error">
			<h2><?php echo htmlentities($error['message']); ?></h2>
			<em>at <?php echo $error['file']; ?> on line <?php echo $error['line']; ?></em>
		</div>

		<div class="code">
			<?php foreach ($code as $i => $line): ?>
				<?php if ($i == $error['line']): ?>
					<div class="highlight"><?php echo $i.': '.str_replace("\t", str_repeat("&nbsp;", 4), htmlentities($line)); ?></div>
				<?php else: ?>
					<div><?php echo $i.': '.str_replace("\t", str_repeat("&nbsp;", 4), htmlentities($line)); ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>

        <div class="stack-trace">
			<h3>Stack Trace</h3>
			<!--pre><?php print_r($error['trace']); ?></pre-->
			<ol reversed>
            <?php foreach ($error['trace'] as $i => $trace): ?>
				<?php if (isset($trace['class']) && $trace['class'] == 'Rocket\Error\System'){
					continue;
				}?>
                <li>
					<div class="src">
                    <?php
						echo isset($trace['class']) ? $trace['class'] : '';
						echo isset($trace['type']) ? $trace['type'] : '';
						echo isset($trace['function']) ? $trace['function'] : 'Unknown function';
					?>()
					</div>
					<div class="file">
						<?php
							echo isset($trace['file']) ? $trace['file'] : 'Unknown file';
							echo ':';
							echo isset($trace['line']) ? $trace['line'] : 'Unknown line';
						?>
					</div>
                    <div class="args">
                        <pre><?php print_r($trace['args']); ?></pre>
                    </div>
                </li>
            <?php endforeach; ?>
			</ol>
        </div>

		<div class="environment">
			<h3>Environment Variables</h3>
			<?php
				echo renderArrayData('$_SERVER', $_SERVER);
				$constants = get_defined_constants(true);
				$constants = $constants['user'];
				echo renderArrayData('CONSTANTS', $constants);
				echo renderArrayData('$_GET', $_GET);
				echo renderArrayData('$_POST', $_POST);
				echo renderArrayData('$_COOKIE', $_COOKIE);
				echo renderArrayData('$_FILES', $_FILES);
				$_SESSION = isset($_SESSION) ? $_SESSION : array('Uninitialized' => 'Session has not been started. Use a session system or session_start().');
				echo renderArrayData('$_SESSION', $_SESSION);
			?>
			<h3>Partial Output</h3>
			<div class="data-row partial-output">
				<?php if (strlen($partial)): ?>
				<pre><?php echo htmlentities(str_replace('<br>', PHP_EOL, $partial)); ?></pre>
				<?php else: ?>
				<pre>No output</pre>
				<?php endif; ?>
			</div>
		</div>


        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.2.min.js"><\/script>')</script>

        <script>
            $(function(){
                $('.stack-trace ol li .src').click(function(e){
                    e.preventDefault();
                    var $li = $(this).closest('li');
                    if ($li.hasClass('open')){
                        $li.removeClass('open');
                    } else {
                        $li.addClass('open');
                    }
                });
            });
        </script>
    </body>
</html>
