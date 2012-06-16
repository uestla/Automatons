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



	/**
	 * @param type state name
	 * @param type is it initial?
	 * @param type is it final?
	 */
	function __construct($name, $initial, $final)
	{
		$this->name = (string) $name;
		$this->initial = (bool) $initial;
		$this->final = (bool) $final;
	}



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
