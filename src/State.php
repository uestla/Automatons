<?php

namespace Automaton;


class State implements Interfaces\State
{
	/** @var string */
	private $name;



	/**
	 * @param type state name
	 */
	function __construct($name)
	{
		$this->name = (string) $name;
	}



	/** @return string */
	function getName()
	{
		return $this->name;
	}
}
