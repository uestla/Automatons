<?php

namespace Automaton;


class State implements Interfaces\State
{
	/** @var string */
	private $name;



	/** @param string state name */
	function __construct($name)
	{
		$this->name = (string) $name;
	}



	/** @return string */
	function getName()
	{
		return $this->name;
	}



	/** @return string state name */
	function __toString()
	{
		return $this->name;
	}
}
