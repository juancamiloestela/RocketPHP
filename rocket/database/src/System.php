<?php
namespace Rocket\Database;
/**
 * Database System for RocketPHP.
 *
 * 
 *
 * @package    Rocket\Database
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Initial implementation
 */

include 'Query.php';

class System
{
	private $dbh = null;
	private $affectedRows = 0;
	private $tables = false;
	private $indexes = array();

	public $config = array();
	protected $defaults = array(
		'driver' => 'mysql',
		'host' => '127.0.0.1',
		'name' => null,
		'username' => null,
		'password' => null,
		'port' => 3306,
		'timezone' => null
	);


	function __construct($config = array())
	{
		$this->config = array_merge($this->defaults, $config);
	}

	function select()
	{
		$columns = func_get_args();
		$instance = new Query($this);
		//TODO: check multiple args functionality
		call_user_func_array(array($instance, 'select'), $columns);
		return $instance;
	}

	function insert($data)
	{
		$instance = new Query($this);
		$instance->insert($data);
		return $instance;
	}

	function update($table)
	{
		$instance = new Query($this);
		$instance->update($table);
		return $instance;
	}

	function deleteFrom($table)
	{
		$instance = new Query($this);
		$instance->deleteFrom($table);
		return $instance;
	}

	function replaceInto($table)
	{
		$instance = new Query($this);
		$instance->replaceInto($table);
		return $instance;
	}


	function connect()
	{
		if (!$this->config['name']){
			//die('Could not connect');
			throw new \Exception('No database name specified');
		}

		if ($this->config['driver'] == 'mysql'){
			$port = $this->config['port'];
			$port = !empty($port) ? $port : '3306';
			$connectionString = 'mysql:host='.$this->config['host'].';port='.$port.';dbname='.$this->config['name'];
		}else{
			throw new \Exception('Invalid database driver');
		}
		// TODO: other drivers!
		//try{
			$this->dbh = new \PDO($connectionString, $this->config['username'], $this->config['password']);
			if ($this->config['timezone']){
				$this->dbh->exec("SET time_zone='".$this->config['timezone']."';");
			}
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);
		/*}catch (\PDOException $e){
			die('Could not connect');
		}*/
	}

	function disconnect()
	{
		$this->dbh = null;
	}

	function __invoke($sql = false, $data = array())
	{
		if (!$sql){
			return $this;
		}
		return $this->query($sql, $data);
	}

	function tables($forceRefresh = false)
	{
		if (!$this->tables || $forceRefresh){
			$this->tables = array();
			$result = $this->query('SHOW tables');

			foreach ($result as $table){
				$t = current($table);
				$this->tables[] = $t;
			}
		}
		return $this->tables;
	}

	function indexes($table, $forceRefresh = false)
	{
		if (!array_key_exists($table, $this->indexes) || $forceRefresh){
			$this->indexes[$table] = array();
			$result = $this->query('SHOW INDEX FROM `'.$table.'`');

			foreach ($result as $index){
				if (!array_key_exists($index['Column_name'], $this->indexes[$table])){
					$this->indexes[$table][$index['Column_name']] = array();
				}
				$this->indexes[$table][$index['Column_name']][] = $index;
			}
		}
		return $this->indexes[$table];
	}

	function schema($table)
	{
		$q = $this->dbh->prepare('DESCRIBE ' . $table);
		$q->execute();
		$result = $q->fetchAll(\PDO::FETCH_ASSOC);
		$schema = array();
		foreach ($result as $key => $column){
			$schema[$column['Field']] = $column;
			$schema[$column['Field']]['Table'] = $table;
		}
		return $schema;
	}

	function table($name)
	{
		$instance = new Query($this);
		$instance->table($name);
		return $instance;
	}

	/*function tableNameFor($name)
	{
		$tables = $this->config['table_to_class_map'];
		foreach ($tables as $table => $object){
			if ($name == $object){
				return $table;
			}
		}
		return $name;
	}

	function objectNameFor($name)
	{
		$tables = $this->config['table_to_class_map'];
		foreach ($tables as $table => $object){
			if ($name === $table){
				return $object;
			}
		}
		return $name;
	}*/

	function __get($name)
	{
		if ($name === 'query'){
			$instance = new Query($this);
			return $instance;
		}else if($name === 'tables') {
			return $this->tables();
		}else{
			return $this->table($name);
		}
	}

	function __call($name, $value)
	{
		if ($this->dbh === null){
			$this->connect();
		}

		if (count($value) && is_numeric($value[0])){
			$instance = new Query($this);
			$instance->select()->from($name)->where('id')->is($value[0]);
			return $instance;
		}else if (method_exists($this->dbh, $name)){
			return call_user_func_array(array($this->dbh, $name), $value);
		}
	}

	function query($sql, $data = array())
	{
		if ($this->dbh === null){
			$this->connect();
		}

		//try {
			//$start = microtime(true);
			$stmt = $this->dbh->prepare($sql);
			//var_dump($sql);die();
			$stmt->execute($data);
			$this->affectedRows = $stmt->rowCount();
			return $stmt->fetchAll(\PDO::FETCH_ASSOC);

			//trace($stmt->errorInfo());
			/*if (preg_match('/INSERT /i', $sql)){
				return new QueryAttempt(true, QueryAttempt::INSERT_SUCCESS, array('id' => $this->lastInsertId()));
			}else if (preg_match('/UPDATE /i', $sql)){
				$this->affectedRows = $stmt->rowCount();
				return new QueryAttempt(true, QueryAttempt::UPDATE_SUCCESS, array('affectedRows' => $this->affectedRows));
			}else if (preg_match('/DELETE /i', $sql)){
				$this->affectedRows = $stmt->rowCount();
				return new QueryAttempt(true, QueryAttempt::DELETE_SUCCESS, array('affectedRows' => $this->affectedRows));
			}else{
				return new QueryAttempt(true, QueryAttempt::FETCH_SUCCESS, $stmt->fetchAll(\PDO::FETCH_ASSOC));
				//return $stmt->fetchAll(\PDO::FETCH_ASSOC);
			}*/
			//$this->fire('database.query', array($sql, $data, (microtime(true) - $start)));
		/*} catch(\PDOException $e) {
			return new QueryAttempt(false, QueryAttempt::FAILED, $e->errorInfo);
		}*/
	}

	/*function exec($sql, $data = array()){
		if ($this->dbh === null){
			$this->connect();
		}

		try {
			$stmt = $this->dbh->prepare($sql);
			$stmt->execute($data);
			return new QueryAttempt(true, QueryAttempt::FETCH_SUCCESS, array('statement' => $stmt));
		} catch(\PDOException $e) {
			return new QueryAttempt(false, QueryAttempt::FAILED, $e->errorInfo);
		}
	}*/

	function lastInsertId()
	{
		if ($this->dbh === null){
			$this->connect();
		}
		return $this->dbh->lastInsertId();
	}

	function quote($value)
	{
		if ($this->dbh)
		{
			return $this->dbh->quote($value);
		}else{
			return "'" . $value . "'";
		}
	}

	function affectedRows()
	{
		return $this->affectedRows;
	}

	function handle()
	{
		return $this->dbh;
	}

	function tableExists($name)
	{
		$tables = $this->tables();
		return in_array($name, $tables);
	}

	function createTable($name)
	{
		$sql = 'CREATE TABLE `'. $name . '` (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
		$this->dbh->exec($sql);
	}

	function deleteTable($name)
	{
		$sql = 'DROP TABLE `' . $name . '`;';
		$this->dbh->exec($sql);
	}

	function addField($field)
	{
		$sql = 'ALTER TABLE `'. $field['Table'] . '` ADD `' . $field['Field'] . '` ' . $field['Type'];
		$sql .= ($field['Null'] == 'NO') ? ' NOT NULL' : ' NULL';
		
		if (is_numeric($field['Default'])){
			$sql .= ' DEFAULT ' . $field['Default'];
		}else if (is_null($field['Default']) && $field['Null'] == 'YES'){
			$sql .= ' DEFAULT NULL';
		}else if (!is_null($field['Default'])){
			$sql .= " DEFAULT '" . $field['Default'] . "'";
		}

echo $sql . PHP_EOL;
		$this->dbh->exec($sql);
	}

	function dropField($field)
	{
		$sql = 'ALTER TABLE `'. $field['Table'] . '` DROP `' . $field['Field'].'`';
		$this->dbh->exec($sql);
	}

	function modifyField($field)
	{
		$sql = 'ALTER TABLE `'. $field['Table'] . '` MODIFY `' . $field['Field'] . '` ' . $field['Type'];
		$sql .= ($field['Null'] == 'NO') ? ' NOT NULL' : ' NULL';

		if (is_numeric($field['Default'])){
			$sql .= ' DEFAULT ' . $field['Default'];
		}else if (is_null($field['Default']) && $field['Null'] == 'YES'){
			$sql .= ' DEFAULT NULL';
		}else if (!is_null($field['Default'])){
			$sql .= " DEFAULT '" . $field['Default'] . "'";
		}


		// update indexes
		$indexes = $this->indexes($field['Table']);

		$isUnique = false;
		if (array_key_exists($field['Field'], $indexes)){
			foreach ($indexes[$field['Field']] as $i => $index){
				if ($index['Non_unique'] == 0){
					$isUnique = true;
				}
			}
		}

		if ($field['Key'] == 'UNI'){
			if (!$isUnique){
				$sql .= '; ALTER TABLE `'. $field['Table'] . '` ADD UNIQUE (' . $field['Field'] . ')';
			}
		}else if ($field['Key'] == 'PRI'){
			// TODO: investigate this scenarios
		}else{
			if ($isUnique){
				$sql .= '; ALTER TABLE `'. $field['Table'] . '` DROP INDEX ' . $field['Field'];
			}
		}
echo $sql . PHP_EOL;
		$this->dbh->exec($sql);
	}

}

/**
 * Copyright (c) 2014 Juan Camilo Estela <juank@revolutiondynamics.co>, others
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */