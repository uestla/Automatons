<?php

namespace Automaton;


class Automaton implements Interfaces\IAutomaton
{
	/** @var IState[] */
	private $states = array();



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



	function & __get($name)
	{
		foreach ($this->states as $state) {
			if ($state->getName() === $name) {
				return $state;
			}
		}

		throw new Exceptions\StateNotFoundException("State '$name' not found.");
	}
}
