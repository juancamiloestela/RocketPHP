<?php

class RocketDatabaseSystemTest extends PHPUnit_Framework_TestCase
{

	protected $system;

	function setUp(){
		$this->system = new RocketDatabaseSystem();
	}

	function tearDown(){

	}

	function testSelectAllQuery(){
		$query = $this->system->query->select('*');
		$expected = 'SELECT * ';
		$this->assertEquals($query->sql(), $expected);
	}

	function testSelectAllFromTableQuery(){
		$query = $this->system->query->select('*')->from('table');
		$expected = 'SELECT * FROM table ';
		$this->assertEquals($query->sql(), $expected);
	}

	function testSelectAllFromTableWhereQuery(){
		$query = $this->system->query->select('*')
										->from('table')
										->where('id')
										->is(3);
		$expected = "SELECT * FROM table WHERE id = 3 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testSelectAllFromTableWhereOrderQuery(){
		$query = $this->system->query->select('*')
										->from('table')
										->where('id')
										->is(3)
										->orderBy('col1');

		$expected = "SELECT * FROM table WHERE id = 3 ORDER BY col1 ASC ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testSelectAllFromTableWhereSortLimitQuery(){
		$query = $this->system->query->select('*')
										->from('table')
										->where('id')
										->is(3)
										->orderBy('col1')
										->limit(1);

		$expected = "SELECT * FROM table WHERE id = 3 ORDER BY col1 ASC LIMIT 0, 1 ";
		$this->assertEquals($query->sql(), $expected);
	}


	function testSelect(){
		$query = $this->system->query->select();
		$expected = "SELECT  ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->select('*');
		$expected = "SELECT * ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->select('col1','col2');
		$expected = "SELECT col1,col2 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->select('col1,col2');
		$expected = "SELECT col1,col2 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testFrom(){
		$query = $this->system->query->from('col1');
		$expected = "FROM col1 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testInsert(){
		$query = $this->system->query->insertInto('table');
		$expected = "INSERT INTO table ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testUpdate(){
		$query = $this->system->query->update('table');
		$expected = "UPDATE table ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testReplaceInto(){
		$query = $this->system->query->replaceInto('table');
		$expected = "REPLACE INTO table ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testWhere(){
		$query = $this->system->query->where('col1');
		$expected = "WHERE col1 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->where('col1')->where('col2');
		$expected = "WHERE col1 AND col2 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->where('col1')->where('col2')->where('col3');
		$expected = "WHERE col1 AND col2 AND col3 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testDeleteFrom(){
		$query = $this->system->query->deleteFrom('table');
		$expected = "DELETE FROM table ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testOnDuplicateKeyUpdate(){
		$data = array('col1' => 'v1', 'col2' => 'v2');
		$query = $this->system->query->onDuplicateKeyUpdate($data);
		$expected = "ON DUPLICATE KEY UPDATE col1 = 'v1', col2 = 'v2' ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testValues(){
		$data = array('col1' => 'v1', 'col2' => 0, 'col3' => 3);
		$query = $this->system->query->values($data);
		$expected = "(col1,col2,col3) VALUES ('v1', 0, 3) ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testSet(){
		$data = array('col1' => 'v1', 'col2' => 0, 'col3' => 3);
		$query = $this->system->query->set($data);
		$expected = "SET col1 = 'v1', col2 = 0, col3 = 3 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testIs(){
		$query = $this->system->query->col1->is(NULL);
		$expected = "col1 IS NULL ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->is('string');
		$expected = "col1 = 'string' ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testIsNot(){
		$query = $this->system->query->col1->isNot(NULL);
		$expected = "col1 IS NOT NULL ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->isNot('string');
		$expected = "col1 != 'string' ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testIsGreaterThan(){
		$query = $this->system->query->col1->isGreaterThan('3');
		$expected = "col1 > 3 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->isGreaterThan(0);
		$expected = "col1 > 0 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->isGreaterThan(-1);
		$expected = "col1 > -1 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testIsLessThan(){
		$query = $this->system->query->col1->isLessThan('3');
		$expected = "col1 < 3 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->isLessThan(0);
		$expected = "col1 < 0 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->isLessThan(-1);
		$expected = "col1 < -1 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testInRange(){
		$query = $this->system->query->col1->inRange('3','5');
		$expected = "col1 BETWEEN 3 and 5 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->inRange(0,5);
		$expected = "col1 BETWEEN 0 and 5 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->inRange(-1,5);
		$expected = "col1 BETWEEN -1 and 5 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testNotInRange(){
		$query = $this->system->query->col1->notInRange('3','5');
		$expected = "col1 NOT BETWEEN 3 and 5 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->notInRange(0,5);
		$expected = "col1 NOT BETWEEN 0 and 5 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->notInRange(-1,5);
		$expected = "col1 NOT BETWEEN -1 and 5 ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testIn(){
		//error
		$query = $this->system->query->col1->in('-1,5');
		$expected = "col1 IN('-1,5') ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->col1->in(-1,5);
		$expected = "col1 IN(-1,5) ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testOrder(){
		$query = $this->system->query->orderBy('col1');
		$expected = "ORDER BY col1 ASC ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->orderBy('col1', 'DESC');
		$expected = "ORDER BY col1 DESC ";
		$this->assertEquals($query->sql(), $expected);
	}

	function testLimits(){
		$query = $this->system->query->limit(0,0);
		$expected = "LIMIT 0, 0 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->limit(1,1);
		$expected = "LIMIT 1, 1 ";
		$this->assertEquals($query->sql(), $expected);

		$query = $this->system->query->limit(1);
		$expected = "LIMIT 0, 1 ";
		$this->assertEquals($query->sql(), $expected);
	}
}