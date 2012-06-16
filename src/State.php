<?php

namespace Automaton;


class State implements Interfaces\IState
{
	/** @var string */
	private $name;

	/** @var bool */
	private $initial = FALSE;

	/** @var bool */
	private $final = FALSE;



	/** @return string */
	function getName()
	{
		return $this->name;
	}



	/** @return bool */
	function isInitial()
	{
		return $this->initial;
	}



	/** @return bool */
	function isFinal()
	{
		return $this->final;
	}
}
