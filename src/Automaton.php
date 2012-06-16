<?php

namespace Automaton;


class Automaton implements Interfaces\Automaton
{
	/** @var Interfaces\State[] */
	private $states = NULL;

	/** @var array */
	private $alphabet = NULL;

	/** @var Interfaces\Transition[] */
	private $transitions = NULL;

	/** @var Interfaces\State[] */
	private $inits = NULL;

	/** @var Interfaces\State[] */
	private $finals = NULL;



	/**
	 * @param Interfaces\State[] state list
	 * @param array alphabet
	 * @param Interfaces\Transition[]
	 * @param Interfaces\State[]
	 * @param Interfaces\State[]
	 */
	function __construct(array $states, array $alphabet, array $transitions, array $inits, array $finals)
	{
		$this->states = $states;
		$this->alphabet = $alphabet;
		$this->setTransitions($transitions);
		$this->setInits($inits);
		$this->setFinals($finals);
	}



	/**
	 * @param  Interfaces\Transition[]
	 * @return Interfaces\Automaton
	 */
	function setTransitions(array $transitions)
	{
		foreach ($transitions as $t) {
			$this->checkStates($t->getTarget());
		}

		$this->transitions = $transitions;
		return $this;
	}



	/**
	 * @param  Interfaces\State[]
	 * @return Interfaces\Automaton provides fluent interface
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function setInits(array $inits)
	{
		$this->checkStates($inits);
		$this->inits = $inits;
		return $this;
	}



	/**
	 * @param  Interfaces\State[]
	 * @return Interfaces\Automaton provides fluent interface
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function setFinals(array $finals)
	{
		$this->checkStates($finals);
		$this->finals = $finals;
		return $this;
	}



	/**
	 * @param  Interfaces\State[]
	 * @return void
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function checkStates(array $states)
	{
		foreach ($states as $state) {
			if (!in_array($state, $this->states, TRUE)) {
				throw new Exceptions\InvalidArgumentException("State '{$state->getName()}' not found in the state array.");
			}
		}
	}



	/** @return Interfaces\Automaton provides fluent interface */
	function determinize()
	{
		// ...
	}



	/** @return Interfaces\Automaton provides fluent interface */
	function minimize()
	{
		// ...
	}



	/**
	 * State getter
	 *
	 * @param  string state name
	 * @return Interfaces\State
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
