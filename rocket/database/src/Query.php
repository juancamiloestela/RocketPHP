<?php
namespace Rocket\Database;
/**
 * Query class to handle a RocketPHP database query.
 *
 * A Query instance represents data request in RocketPHP.
 * The class handles the most common methods for building
 * a query and also allows a fallback to write plain SQL.
 *
 * @package    Rocket\Query
 * @author     Juan Camilo Estela <juank@revolutiondynamics.co>
 * @copyright  2014 Juan Camilo Estela
 * @license    MIT
 *
 * @version    0.1.0 Initial implementation
 */



class Query implements \ArrayAccess, \Iterator, \Countable
{
	private $system;

	protected $changed = false;
	protected $operation;
	protected $table;
	protected $columns = array();
	protected $where = array();
	protected $offset = false;
	protected $count = false;
	protected $data = array();
	protected $insertData = array();
	protected $updateData = array();
	protected $orderBy = array();
	protected $recordsPerPage = 10;
	protected $join = array();
	protected $onDuplicateKeyUpdateData = array();
	
	protected $result = array();
	protected $index = false;

	function __construct($system)
	{
		$this->system = $system;//\Rocket::system('Rocket\DatabaseSystem');
		$this->select('*');
	}


	function table($name)
	{
		$this->table = $name;
		$this->changed = true;
		return $this;
	}

	function from($name)
	{
		return $this->table($name);
	}

	function into($name)
	{
		return $this->table($name);
	}

	function columns()
	{
		$args = func_get_args();
		if (count($args) == 1 && is_array($args[0])){
			$columns = $args[0];
		}else{
			$columns = $args;
		}

		$this->columns = $columns;
		$this->changed = true;
		return $this;
	}

	function select($columns = '*')
	{
		$this->columns($columns);
		$this->operation = 'SELECT {columns} FROM {table} ';
		$this->changed = true;
		return $this;
	}

	function update($data)
	{
		$this->updateData = $data;
		$this->operation = 'UPDATE {table} SET {data} ';
		$this->changed = true;
		return $this;
	}

	function insert($data)
	{
		$this->insertData = $data;
		$this->operation = 'INSERT INTO {table} ({data.keys}) VALUES ({data.values})';
		$this->changed = true;
		return $this;
	}

	function delete()
	{
		$this->operation = 'DELETE FROM {table} ';
		$this->changed = true;
		return $this;
	}

	function where($opener, $column = false, $operator = 'AND')
	{
		if (!$column){
			$column = $opener;
			$opener = false;
		}

		if (count($this->where) == 0){
			$operator = 'WHERE';
		}

		$this->where[] = array($operator, $opener, $column, null, null, null);
		$this->changed = true;
		return $this;
	}

	private function _and($opener, $column = false)
	{
		return $this->where($opener, $column, 'AND');
	}

	private function _or($opener, $column = false)
	{
		return $this->where($opener, $column, 'OR');
	}

	private function &getLastCondition()
	{
		$this->changed = true;
		end($this->where);
		return $this->where[key($this->where)];
	}

	function is($value, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ($value === null) ? ' IS ' : ' = ';
		$condition[4] = $value;
		$condition[5] = $closer;
		return $this;
	}

	function isNot($value, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ($value === null) ? ' IS NOT ' : ' <> ';
		$condition[4] = $value;
		$condition[5] = $closer;
		return $this;
	}

	function isIn($values, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ' IN(';
		$condition[4] = $values;
		$condition[5] = ' )'.$closer;
		return $this;
	}

	function isGreaterThan($value, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ' > ';
		$condition[4] = $values;
		$condition[5] = $closer;
		return $this;
	}

	function isLessThan($value, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ' < ';
		$condition[4] = $values;
		$condition[5] = $closer;
		return $this;
	}

	function isInRange($min, $max, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ' BETWEEN ';
		$condition[4] = array($min, $max);
		$condition[5] = $closer;
		$condition[6] = ' and ';
		return $this;
	}

	function isNotInRange($min, $max, $closer = false)
	{
		$condition = &$this->getLastCondition();
		$condition[3] = ' NOT BETWEEN ';
		$condition[4] = array($min, $max);
		$condition[5] = $closer;
		$condition[6] = ' and ';
		return $this;
	}

	function limit($offset, $count = false)
	{
		$this->changed = true;
		if (!$count){
			$count = $offset;
			$offset = 0;
		}
		$this->offset = $offset;
		$this->count = $count;

		return $this;
	}

	function recordsPerPage($count)
	{
		$this->changed = true;
		$this->recordsPerPage = $count;
		return $this;
	}

	function page($page){
		$this->changed = true;
		$this->limit($page * $this->recordsPerPage, $this->recordsPerPage);
		return $this;
	}

	function orderBy($column, $direction = 'asc')
	{
		$this->changed = true;
		$this->orderBy[] = array($column, $direction);
		return $this;
	}

	function join($table, $type = '')
	{
		$this->join[$table] = array($type);
		return $this;
	}

	function on($columnA, $columnB)
	{
		end($this->join);
		$key = key($this->join);
		$this->join[$key][1] = $columnA;
		$this->join[$key][2] = $columnB;
		return $this;
	}

	function onDuplicateKeyUpdate($data)
	{
		$this->onDuplicateKeyUpdateData = $data;
		//$this->operation = 'ON DUPLICATE KEY UPDATE {data} ';
		$this->changed = true;
		return $this;
	}

	function sql()
	{
		$opCount = 0;

		$sql = $this->operation;
		$sql = str_replace('{columns}', implode(',', $this->columns), $sql);
		$sql = str_replace('{table}', $this->table, $sql);

		if (count($this->join)){
			foreach ($this->join as $table => $join){
				$sql .= ' ' . $join[0] . ' JOIN ' . $table . ' ON ' . $this->table . '.' . $join[1] . ' = ' . $table . '.' . $join[2];
			}
		}

		if (count($this->insertData)){
			$opCount++;
			$insertKeys = array();
			$insertValues = array();
			foreach ($this->insertData as $key => $value){
				$k = $key . '_' . $opCount;
				$insertKeys[] = $key;
				$insertValues[] = ':' . $k;
				$this->data[$k] = $value;
			}
			$sql = str_replace('{data.keys}', implode(',', $insertKeys), $sql);
			$sql = str_replace('{data.values}', implode(',', $insertValues), $sql);
		}

		if (count($this->updateData)){
			$opCount++;
			$data = array();
			foreach($this->updateData as $key => $value){
				$k = $key . '_' . $opCount;
				$data[] = $key . ' = :' . $k;
				$this->data[$k] = $value;
			}
			$sql = str_replace('{data}', implode(',', $data), $sql);
		}

		if (count($this->where)){
			foreach ($this->where as $condition){
				if (!is_array($condition)){
					$sql .= $condition;
					continue;
				}

				$opCount++;
				$key = $condition[2] . '_' . $opCount;
				if (is_array($condition[4])){
					foreach ($condition[4] as $subKey => $subValue){
						$this->data[str_replace('.', 'DOT', $key.'_'.$subKey)] = $subValue;
					}
				}else{
					$this->data[str_replace('.', 'DOT', $key)] = $condition[4];
				}
				
				$sql .= ' ' . $condition[0] . ' ';
				if ($condition[1]){
					$sql .= $condition[1];
				}
				$sql .= $condition[2] . ' ' . $condition[3] . ' ';
				if (is_array($condition[4])){
					$keys = array();
					foreach ($condition[4] as $subKey => $subValue){
						array_push($keys, ':' . str_replace('.', 'DOT', $key) . '_' . $subKey);
					}
					$separator = (isset($condition[6])) ? $condition[6] : ', ';
					$sql .= implode($separator, $keys);
				}else{
					$sql .= ':' . str_replace('.', 'DOT', $key);
				}
				if ($condition[5]){
					$sql .= $condition[5];
				}
			}
		}

		if (count($this->onDuplicateKeyUpdateData)){
			$sql .= ' ON DUPLICATE KEY UPDATE ';
			$opCount++;
			$data = array();
			foreach($this->onDuplicateKeyUpdateData as $key => $value){
				$k = $key . '_' . $opCount;
				$data[] = $key . ' = :' . $k;
				$this->data[$k] = $value;
			}
			$sql .= implode(',', $data);
		}

		if (count($this->orderBy)){
			$sql .= ' ORDER BY ';
			$conditions = array();
			foreach ($this->orderBy as $condition){
				$conditions[] = $condition[0] . ' ' . $condition[1];
			}
			$sql .= implode(',', $conditions);
		}

		if ($this->offset !== false){
			$sql .= ' LIMIT ' . $this->offset .', ' . $this->count;
		}

		return $sql;
	}

	function sqlWithData()
	{
		$sql = $this->sql();
		$data = $this->data();
		foreach ($data as $key => $value){
			if ($value === null){
				$value = 'NULL';
			}else if(is_numeric($value)){
				$value = $value;
			}else{
				$value = "'".$value."'";
			}
			$sql = str_replace(':'.$key, $value, $sql);
		}
		return $sql;
	}

	function data()
	{
		return $this->data;
	}

	function isDirty()
	{
		return $this->changed;
	}

	function result()
	{
		if ($this->changed){
			$this->result = $this->system->query($this->sql(), $this->data);
			$this->changed = false;

			/*if (count($this->insertData)){
				return $this->system->lastInsertId();
			}else if (count($this->updateData)){
				return $this->system->affectedRows();
			}*/
			/*if (preg_match('/INSERT INTO/', $this->sql)){
				$this->result = $this->system->lastInsertId();
			}else if(preg_match('/UPDATE /', $this->sql)){
				$this->result = $this->system->affectedRows();
			}*/
		}
		return $this->result;
	}

	/*function asArray()
	{
		return $this->result();
	}*/

	function __get($name)
	{
		$result = $this->result();

		if ($name == 'where'){
			return $this;
		}
		if (count($result) == 1){
			if (isset($result[0][$name])){
				return $result[0][$name];
			}
		}else if (isset($result[$name])){
			return $result[$name];
		}else if (isset($result[$name .'_id'])){
			$tableName = $this->system->tableNameFor($name);
			return $this->system->$tableName($result[$name.'_id']);
		}else{
			// TODO: clean this up or document it!
			/*if (count($this->result) === 0){
				return $this;
			}*/
			$tableName = $this->system->tableNameFor($name);
			$objectName = $this->system->objectNameFor($this->table);
			//var_dump($this->result);
			if (count($this->result) === 0){
				$id = -1;
			}else{
				$id = $this->id;
			}
			//var_dump($id);
			return $this->system->$tableName->where($objectName.'_id')->is($id);
		}
	}

	function __call($name, $args)
	{
		if ($name == 'and'){
			return call_user_func_array(array($this,'_and'), $args);
		}else if ($name == 'or'){
			return call_user_func_array(array($this,'_or'), $args);
		}
	}

	function __toString()
	{
		return $this->sql();
	}

	function __invoke()
	{
		$this->result();
		return $this;
	}

	// Array Access
	function offsetSet($offset, $value) 
	{
		$this->result();
		$this->result[$offset] = $value;
	}
	
	function offsetExists($offset) 
	{
		$this->result();
		return isset($this->result[$offset]);
	}

	function offsetUnset($offset) 
	{
		$this->result();
		unset($this->result[$offset]);
	}

	function offsetGet($offset) 
	{
		$this->result();
		if (is_numeric($offset) || $offset === false){
			$this->index = $offset;
		}else{
			return $this->__get($offset);
		}
		return $this;
	}

	// Iterator
	function rewind()
	{
		$this->result();
		reset($this->result);
		return $this;
	}

	function current()
	{
		$this->result();
		//current($this->result);
		$this->index = key($this->result);
		return $this;
	}

	function key()
	{
		$this->result();
		return key($this->result);
	}

	function next()
	{
		$this->result();
		next($this->result);
		return $this;
	}

	function valid()
	{
		$this->result();
		return key($this->result) !== null;
	}

	// Countable
	function count()
	{
		$this->result();
		return count($this->result);
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