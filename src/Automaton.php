<?php

namespace Automaton;


class Automaton implements Interfaces\IAutomaton
{
	/** @var IState[] */
	private $states = array();



	/**
	 * @param array state list
	 */
	function __construct(array $states)
	{
		$this->states = $states;
	}



	/** @return Automaton */
	function determinize()
	{
		// ...
	}



	/** @return Automaton */
	function minimize()
	{
		// ...
	}



	/**
	 * State getter
	 *
	 * @param  string state name
	 * @return IState
	 * @throws Exceptions\StateNotFoundException
	 */
	function & __get($name)
	{
		foreach ($this->states as $state) {
			if ($state->getName() === $name) {
				return $state;
			}
		}

		throw new Exceptions\StateNotFoundException("State '$name' not found.");
	}



	/**
	 * State existence tester
	 *
	 * @param  string state name
	 * @return bool
	 */
	function __isset($name)
	{
		foreach ($this->states as $state) {
			if ($state->getName() === $name) {
				return TRUE;
			}
		}

		return FALSE;
	}
}
