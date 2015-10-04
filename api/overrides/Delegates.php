<?php

class Delegates{

	static function receiveName(&$value, &$errors){
		echo __METHOD__ . PHP_EOL;
		echo "value: " . PHP_EOL; print_r($value); echo PHP_EOL;
		echo "errors: " . PHP_EOL; print_r($errors);
		echo PHP_EOL . PHP_EOL;
		$value = $value . '_name';
		return $value;
	}

	static function publicPatientsError(&$data, &$errors, $mail){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo "errors: " . PHP_EOL; print_r($errors);
		echo "mail: " . PHP_EOL; print_r($mail);
		echo PHP_EOL . PHP_EOL;
		$errors[] = 'injected error';
		return true; // enables/disables exception
	}
	static function publicPatientsInput(&$data, $mail){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo "mail: " . PHP_EOL; print_r($mail);
		echo PHP_EOL . PHP_EOL;
	}
	static function publicPatientsQuery($query, $data){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo "query: " . PHP_EOL; print_r($query);
		echo PHP_EOL . PHP_EOL;
	}
	static function publicPatientsData($data, $mail){
		echo __METHOD__ . PHP_EOL;
		echo "data: " . PHP_EOL; print_r($data);
		echo PHP_EOL . PHP_EOL;
		print_r($mail);
	}
}