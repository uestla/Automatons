<?php

namespace Automaton;


class Automaton implements Interfaces\Automaton
{
	/** @var Interfaces\State[] */
	private $states = NULL;

	/** @var array */
	private $alphabet = NULL;

	/** @var Interfaces\State[] */
	private $inits = NULL;



	/**
	 * @param Interfaces\State[] state list
	 * @param array alphabet
	 * @param Interfaces\State[]
	 */
	function __construct(array $states, array $alphabet, array $inits)
	{
		$this->states = $states;
		$this->alphabet = $alphabet;
		$this->setInits($inits);
	}



	/**
	 * @param  Interfaces\State[]
	 * @return Automaton provides fluent interface
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function setInits(array $inits)
	{
		foreach ($inits as $state) {
			if (!in_array($state, $this->states, TRUE)) {
				throw new Exceptions\InvalidArgumentException("Initial state '{$state->getName()}' not found in the state array.");
			}
		}

		$this->inits = $inits;
		return $this;
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
