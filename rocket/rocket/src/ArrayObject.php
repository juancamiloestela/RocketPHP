<?php
namespace Rocket;

class ArrayObject extends \ArrayObject{

	function offsetGet($offset){
		if (parent::offsetExists($offset)){
			return parent::offsetGet($offset);
		}
		return null;
	}

	function clear(){
		$this->exchangeArray(array());
	}
}