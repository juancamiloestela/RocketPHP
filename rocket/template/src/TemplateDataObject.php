<?php
namespace Rocket\Template;


class TemplateDataObject implements \ArrayAccess, \Countable, \Iterator{

	private $data = array();

	public function __construct($data = array()){
		$this->data = (array)$data;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->data[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	public function offsetGet($offset) {
		if (isset($this->data[$offset])){
			return $this->data[$offset];
		}else{
			return new TemplateDataObject();
		}
	}

	public function &__get($key) {
		if (isset($this->data[$key])){
			return $this->data[$key];
		}else{
			$t = new TemplateDataObject();
			return $t;
		}
	}

	public function __set($key,$value) {
		$this->offsetSet($key, $value);
	}

	public function __isset ($key) {
		return $this->offsetExists($key);
	}

	public function __unset($key) {
		$this->offsetUnset($key);
	}

	public function rewind() {
		return reset($this->data);
	}

	public function current() {
		return current($this->data);
	}

	public function key() {
		return key($this->data);
	}

	public function next() {
		return next($this->data);
	}

	public function valid() {
		return key($this->data) !== null;
	}

	public function count(){
		return count($this->data);
	}

	public function toArray() {
		return $this->data;
	}

	public function __toString(){
		return '';
	}
}