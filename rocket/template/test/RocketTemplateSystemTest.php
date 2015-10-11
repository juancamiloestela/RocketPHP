<?php

class RocketTemplateSystemTest extends PHPUnit_Framework_TestCase
{

	static $system;
	static $tmpFolder = '/tmp';

	static function setUpBeforeClass()
	{
		static::$system = new Rocket\Template\System();
		static::$system->config('label_templates', true);
		static::$system->config('force_recompile', true);

		static::$system->config('app_path', __DIR__ . static::$tmpFolder . '/');

		if (!file_exists(__DIR__ . static::$tmpFolder)){
			mkdir(__DIR__ . static::$tmpFolder);
		}
	}

	static function tearDownAfterClass()
	{
		
	}

	function setUp()
	{
		
	}

	function tearDown()
	{
		
	}

	function testTemplateWithBasicString()
	{
		$expected = 'hello';
		$compiled = static::$system->compile('hello');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testTemplateWithBasicStringAndPlaceholder()
	{
		$expected = 'hello <?php if (isset($who)){echo $who;} ?>';
		$compiled = static::$system->compile('hello $who');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello<?php if (isset($who)){echo $who;} ?>';
		$compiled = static::$system->compile('hello$who');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($who)){echo $who;} ?> and welcome';
		$compiled = static::$system->compile('hello $who and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($who)){echo $who;} ?>and welcome';
		$compiled = static::$system->compile('hello $who;and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello<?php if (isset($who)){echo $who;} ?>and welcome';
		$compiled = static::$system->compile('hello$who;and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($who)){echo $who;} ?>! and welcome';
		$compiled = static::$system->compile('hello $who! and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($who)){echo $who;} ?>? and welcome';
		$compiled = static::$system->compile('hello $who? and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($$who)){echo $$who;} ?> and welcome';
		$compiled = static::$system->compile('hello $$who and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($_who)){echo $_who;} ?> and welcome';
		$compiled = static::$system->compile('hello $_who and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($who)){echo $who;} ?>'.PHP_EOL.' and welcome';
		$compiled = static::$system->compile('hello $who'.PHP_EOL.' and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testSingleLineComment()
	{
		$expected = 'hello  and welcome';
		$compiled = static::$system->compile('hello /#$who; #/ and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testMultiLineComment()
	{
		$expected = 'hello  and '.PHP_EOL.'welcome';
		$compiled = static::$system->compile('hello /#$who; '.PHP_EOL.' <if condition="true">hello</if> #/ and '.PHP_EOL.'welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testOneCharVarName()
	{
		$expected = 'hello <?php if (isset($o)){echo $o;} ?> and welcome';
		$compiled = static::$system->compile('hello $o and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testPlaceholderThatStartsWithANumber()
	{
		$expected = 'hello $1placeholder';
		$compiled = static::$system->compile('hello $1placeholder');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testEscapedPlaceholders()
	{
		$expected = 'hello <?php if (isset($who)){echo $who;} ?> you have won $10!';
		$compiled = static::$system->compile('hello $who you have won \$10!');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello $who you have won $10!';
		$compiled = static::$system->compile('hello \$who you have won $10!');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testMethodCall()
	{
		$expected = 'hello method()';
		$compiled = static::$system->compile('hello method()');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $this->method(); ?>';
		$compiled = static::$system->compile('hello $this->method()');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $this->method(); ?>';
		$compiled = static::$system->compile('hello $this.method()');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method(); ?>';
		$compiled = static::$system->compile('hello $a->_method()');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method(); ?>';
		$compiled = static::$system->compile('hello $a._method()');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method(\'string\'); ?>';
		$compiled = static::$system->compile('hello $a->_method(\'string\')');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method("string"); ?>';
		$compiled = static::$system->compile('hello $a->_method("string")');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method(123.45); ?>';
		$compiled = static::$system->compile('hello $a->_method(123.45)');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method(array(\'a\' => 5)); ?>';
		$compiled = static::$system->compile('hello $a->_method(array(\'a\' => 5))');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar)');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar[5]); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar[5])');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar["key"]); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar["key"])');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar[5][6]); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar[5][6])');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar["key"]["subkey"]); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar["key"]["subkey"])');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar[5][$i]); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar[5][$i])');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar["key"][$i]); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar["key"][$i])');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method(); ?>';
		$compiled = static::$system->compile('hello $a._method()');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar["key"][$i], 5, \'string\', null, $obj); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar["key"][$i], 5, \'string\', null, $obj)');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->_method($aVar["key"][$i], 5, \'string\', null, $obj)->action($t, 5, null); ?>';
		$compiled = static::$system->compile('hello $a->_method($aVar["key"][$i], 5, \'string\', null, $obj)->action($t, 5, null)');
		$this->assertEquals($expected, $compiled, 'Error');

	}

	function testArrayCall()
	{
		$expected = 'hello method[]';
		$compiled = static::$system->compile('hello method[]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($this->arr)){echo $this->arr;} ?>[]';
		$compiled = static::$system->compile('hello $this->arr[]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($this->arr)){echo $this->arr;} ?>[]';
		$compiled = static::$system->compile('hello $this.arr[]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr)){echo $a->arr;} ?>[]';
		$compiled = static::$system->compile('hello $a->arr[]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr)){echo $a->arr;} ?>[]';
		$compiled = static::$system->compile('hello $a.arr[]');
		$this->assertEquals($expected, $compiled, 'Error');


		$expected = 'hello <?php if (isset($this->arr[3])){echo $this->arr[3];} ?>';
		$compiled = static::$system->compile('hello $this->arr[3]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($this->arr[3])){echo $this->arr[3];} ?>';
		$compiled = static::$system->compile('hello $this.arr[3]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[3])){echo $a->arr[3];} ?>';
		$compiled = static::$system->compile('hello $a->arr[3]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[3])){echo $a->arr[3];} ?>';
		$compiled = static::$system->compile('hello $a.arr[3]');
		$this->assertEquals($expected, $compiled, 'Error');


		$expected = 'hello <?php if (isset($a->arr[\'string\'])){echo $a->arr[\'string\'];} ?>';
		$compiled = static::$system->compile('hello $a->arr[\'string\']');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr["string"])){echo $a->arr["string"];} ?>';
		$compiled = static::$system->compile('hello $a->arr["string"]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[12345])){echo $a->arr[12345];} ?>';
		$compiled = static::$system->compile('hello $a->arr[12345]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar])){echo $a->arr[$aVar];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar[5]])){echo $a->arr[$aVar[5]];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar[5]]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar["key"]])){echo $a->arr[$aVar["key"]];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar["key"]]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar[5][6]])){echo $a->arr[$aVar[5][6]];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar[5][6]]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar["key"]["subkey"]])){echo $a->arr[$aVar["key"]["subkey"]];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar["key"]["subkey"]]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar[5][$i]])){echo $a->arr[$aVar[5][$i]];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar[5][$i]]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr[$aVar["key"][$i]])){echo $a->arr[$aVar["key"][$i]];} ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar["key"][$i]]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->arr)){echo $a->arr;} ?>[]';
		$compiled = static::$system->compile('hello $a.arr[]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->method($aVar["key"][$i])->arr[$t])){echo $a->method($aVar["key"][$i])->arr[$t];} ?>';
		$compiled = static::$system->compile('hello $a->method($aVar["key"][$i])->arr[$t]');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php echo $a->arr[$aVar["key"][$i]]->action($t, 5, null); ?>';
		$compiled = static::$system->compile('hello $a->arr[$aVar["key"][$i]]->action($t, 5, null)');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if (isset($a->method($aVar["key"][$i]->method($array[4]))->arr[$t])){echo $a->method($aVar["key"][$i]->method($array[4]))->arr[$t];} ?>';
		$compiled = static::$system->compile('hello $a->method($aVar["key"][$i]->method($array[4]))->arr[$t]');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testPhpTag()
	{
		$expected = 'hello <?php if ($myVar){echo $myVar;} ?> and welcome';
		$compiled = static::$system->compile('hello <?php if ($myVar){echo $myVar;} ?> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if ($myVar){'.PHP_EOL.'echo $myVar;'.PHP_EOL.'} ?> and welcome';
		$compiled = static::$system->compile('hello <?php if ($myVar){'.PHP_EOL.'echo $myVar;'.PHP_EOL.'} ?> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testIfTag()
	{
		$expected = 'hello <?php if ($error): ?>Error: <?php if (isset($message)){echo $message;} ?><?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">Error: $message</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if ($error): ?>'.PHP_EOL.'Error: <?php if (isset($message)){echo $message;} ?>'.PHP_EOL.'<?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">'.PHP_EOL.'Error: $message'.PHP_EOL.'</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testNestedIfTags()
	{
		$expected = 'hello <?php if ($error): ?>Error: <?php if (isset($message)){echo $message;} ?><?php if ($warning): ?>Icon<?php endif; ?><?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">Error: $message<if condition="$warning">Icon</if></if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if ($error): ?>'.PHP_EOL.'Error: <?php if (isset($message)){echo $message;} ?>'.PHP_EOL.'<?php if ($warning): ?>'.PHP_EOL.'Icon'.PHP_EOL.'<?php endif; ?>'.PHP_EOL.'<?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">'.PHP_EOL.'Error: $message'.PHP_EOL.'<if condition="$warning">'.PHP_EOL.'Icon'.PHP_EOL.'</if>'.PHP_EOL.'</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testElseTag()
	{
		$expected = 'hello <?php if ($error): ?>Error: <?php if (isset($message)){echo $message;} ?><?php else: ?>No error!<?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">Error: $message<else>No error!</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if ($error): ?>'.PHP_EOL.'Error: <?php if (isset($message)){echo $message;} ?>'.PHP_EOL.'<?php else: ?>'.PHP_EOL.'No error!'.PHP_EOL.'<?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">'.PHP_EOL.'Error: $message'.PHP_EOL.'<else>'.PHP_EOL.'No error!'.PHP_EOL.'</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testElseIfTag()
	{
		$expected = 'hello <?php if ($error): ?>Error: <?php if (isset($message)){echo $message;} ?><?php elseif ($warning): ?>Something happened<?php else: ?>No error!<?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">Error: $message<elseif condition="$warning">Something happened<else>No error!</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'hello <?php if ($error): ?>'.PHP_EOL.'Error: <?php if (isset($message)){echo $message;} ?>'.PHP_EOL.'<?php elseif ($warning): ?>'.PHP_EOL.'Something happened'.PHP_EOL.'<?php else: ?>'.PHP_EOL.'No error!'.PHP_EOL.'<?php endif; ?> and welcome';
		$compiled = static::$system->compile('hello <if condition="$error">'.PHP_EOL.'Error: $message'.PHP_EOL.'<elseif condition="$warning">'.PHP_EOL.'Something happened'.PHP_EOL.'<else>'.PHP_EOL.'No error!'.PHP_EOL.'</if> and welcome');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testExtendsTag()
	{

		file_put_contents(__DIR__ . static::$tmpFolder . '/layout.php', '<html><body><block name="content"><block name="footer"/></body></html>');

		$expected = array(
			'layout' => array(
				'output' => '<html><body><?php if (isset($_blocks["content"])){ include $this->config("app_path") . $_blocks["content"];} ?><?php if (isset($_blocks["footer"])){ include $this->config("app_path") . $_blocks["footer"];} ?></body></html>',
				'path' => '/layout.php'
			),
			'blocks' => array(
				'content' => array(
					'output' => '<!-- content -->hello <?php if ($error): ?>Error: <?php if (isset($message)){echo $message;} ?><?php elseif ($warning): ?>Something happened<?php else: ?>No error!<?php endif; ?> and welcome<!-- END content -->',
					'name' => "content"
				),
				'footer' => array(
					'output' => "<!-- footer -->footer<!-- END footer -->",
					'name' => "footer"
				)
			)
		);

		$compiled = static::$system->compile('<extends layout="/layout.php">'.PHP_EOL.'<block name="content">hello <if condition="$error">Error: $message<elseif condition="$warning">Something happened<else>No error!</if> and welcome</block><block name="footer">footer</block>');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testRepeatTag()
	{
		// for each
		$expected = 'blah <?php foreach ($object->items as $i => $value):$engine->data["i"] = $i; $engine->data["value"] = $value; ?><li><?php if (isset($i)){echo $i;} ?>: <?php if (isset($value)){echo $value;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat foreach="$object->items" key="$i" value="$value"><li>$i: $value</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php foreach ($object->items(\'eqe\', 5, $x) as $i => $value):$engine->data["i"] = $i; $engine->data["value"] = $value; ?><li><?php if (isset($i)){echo $i;} ?>: <?php if (isset($value)){echo $value;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat foreach="$object->items(\'eqe\', 5, $x)" key="$i" value="$value"><li>$i: $value</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php foreach ($object->items() as $i => $value):$engine->data["i"] = $i; $engine->data["value"] = $value; ?><li><?php if (isset($i)){echo $i;} ?>: <?php if (isset($value)){echo $value;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat foreach="$object->items()" key="$i" value="$value"><li>$i: $value</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php foreach ($object->items[\'index\'] as $i => $value):$engine->data["i"] = $i; $engine->data["value"] = $value; ?><li><?php if (isset($i)){echo $i;} ?>: <?php if (isset($value)){echo $value;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat foreach="$object->items[\'index\']" key="$i" value="$value"><li>$i: $value</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php foreach ($object->items[3] as $i => $value):$engine->data["i"] = $i; $engine->data["value"] = $value; ?><li><?php if (isset($i)){echo $i;} ?>: <?php if (isset($value)){echo $value;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat foreach="$object->items[3]" key="$i" value="$value"><li>$i: $value</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		// for from to
		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range((int)2,(int)5); foreach ($range_0 as $n): $engine->data["n"] = $n; ?><li>iteration <?php if (isset($n)){echo $n;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat from="2" to="5" var="$n"><li>iteration $n</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range((int)2,(int)5); foreach ($range_0 as $i => $n): $engine->data["i"] = $i;$engine->data["n"] = $n; ?><li>iteration <?php if (isset($n)){echo $n;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat from="2" to="5" index="$i" var="$n" ><li>iteration $n</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range((int)$a,(int)$obj->method(6)); foreach ($range_0 as $n): $engine->data["n"] = $n; ?><li>iteration <?php if (isset($n)){echo $n;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat from="$a" to="$obj->method(6)" var="$n"><li>iteration $n</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		// for loops
		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range(1,(int)5); foreach ($range_0 as $x):$engine->data["x"] = $x; ?><li>loop <?php if (isset($x)){echo $x;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat loops="5" var="$x" ><li>loop $x</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range(1,(int)$a); foreach ($range_0 as $x):$engine->data["x"] = $x; ?><li>loop <?php if (isset($x)){echo $x;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat loops="$a" var="$x" ><li>loop $x</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range(1,(int)$a->method(7)); foreach ($range_0 as $x):$engine->data["x"] = $x; ?><li>loop <?php if (isset($x)){echo $x;} ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat loops="$a->method(7)" var="$x" ><li>loop $x</li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');


		// nested loops
		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range(1,(int)$a); foreach ($range_0 as $x):$engine->data["x"] = $x; ?><li>loop <?php foreach ($obj as $j => $val):$engine->data["j"] = $j; $engine->data["val"] = $val; ?><?php if (isset($x)){echo $x;} ?> <?php if (isset($j)){echo $j;} ?>: <?php if (isset($val)){echo $val;} ?><?php endforeach; ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat loops="$a" var="$x" ><li>loop <repeat foreach="$obj" key="$j" value="$val">$x $j: $val</repeat></li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		static::$system->repeats = 0;
		$expected = 'blah <?php $range_0 = range(1,(int)$a); foreach ($range_0 as $x):$engine->data["x"] = $x; ?><li>loop <?php $range_1 = range((int)$low,(int)80); foreach ($range_1 as $val): $engine->data["val"] = $val; ?><?php if (isset($x)){echo $x;} ?>: <?php if (isset($val)){echo $val;} ?><?php endforeach; ?></li><?php endforeach; ?> etc';
		$compiled = static::$system->compile('blah <repeat loops="$a" var="$x" ><li>loop <repeat from="$low" to="80" var="$val">$x: $val</repeat></li></repeat> etc');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testIncludeTag()
	{
		$expected = 'blah <?php include "../path/to/include.php"; ?> etc';
		$compiled = static::$system->compile('blah <include file="path/to/include.php"> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php include "../path/to/include.php"; ?> etc';
		$compiled = static::$system->compile('blah <include file="path/to/include.php" compile="false"> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php include "'.static::$system->config('app_path').'cache/path/to/include.php"; ?> etc';
		$compiled = static::$system->compile('blah <include file="path/to/include.php" compile="true"> etc');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testTemplateTag()
	{
		$expected = 'blah <?php call_user_func(function($engine, $src) { include $engine->config("app_path") . "cache/" . $src;}, $engine,"path/to/template.php"); ?> etc';
		$compiled = static::$system->compile('blah <template src="path/to/template.php"> etc');
		$this->assertEquals($expected, $compiled, 'Error');

		$expected = 'blah <?php call_user_func(function($engine, $src, $somevar, $anothervar) { include $engine->config("app_path") . "cache/" . $src;}, $engine,"path/to/template.php","3",(isset($name) ? $name : null)); ?> etc';
		$compiled = static::$system->compile('blah <template src="path/to/template.php" somevar="3" anothervar="$name"> etc');
		$this->assertEquals($expected, $compiled, 'Error');
	}

	function testCompileFiles()
	{
		
	}

	function testHelperFunctions()
	{
		
	}
}