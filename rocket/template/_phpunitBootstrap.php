<?php

class HiddenFilesIteratorFilter extends RecursiveFilterIterator
{
    public function accept() {
        return !preg_match('/^\./', $this->current()->getFilename());
    }
}


function autoload_class($class)
{
	$directoryIterator = new RecursiveDirectoryIterator(__DIR__);
	$filterIterator = new HiddenFilesIteratorFilter($directoryIterator);
	$iterator = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);
	foreach ($iterator as $filePath => $fileInfo) {
	    if ($class . '.php' == $fileInfo->getFilename()){
	    	if (!class_exists($class)){
	    		require_once $fileInfo->getPathname();
	    	}
	    }
	}
}

spl_autoload_register('autoload_class');

require_once 'rocket.php';
$app = new Rocket();