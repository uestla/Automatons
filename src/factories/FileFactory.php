<?php

namespace Automaton;


class FileFactory implements IFactory
{
	/** @var string */
	private $file;



	function __construct($file)
	{
		$this->file = $file;
	}



	/** @return Automaton */
	function create()
	{
		$path = realpath($this->file);
		if ($path === FALSE) {
			throw new FileNotFoundException("File '{$this->file}' not found.");
		}

		$lines = file($path);
		var_dump($lines); die();
	}
}



class FileNotFoundException extends \Exception {}
