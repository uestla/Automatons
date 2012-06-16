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
		$this->alphabet = array_unique($alphabet);
		sort($this->alphabet);
		$this->setTransitions($transitions);
		$this->setInits($inits);
		$this->setFinals($finals);
	}



	/**
	 * @param  Interfaces\Transition[]
	 * @return Interfaces\Automaton
	 * @throws Exceptions\InvalidArgumentException
	 */
	function setTransitions(array $transitions)
	{
		$tmp = array();

		foreach ($transitions as $t) {
			$this->checkStates($t->getState());

			if (!isset($tmp[$t->getState()->getName()])) {
				$tmp[$t->getState()->getName()] = array();

			} elseif (isset($tmp[$t->getState()->getName()][$t->getLetter()])) {
				throw new Exceptions\InvalidArgumentException("Multiple transition from state '{$t->getState()->getName()}' for letter '{$t->getLetter()}'.");
			}

			$tmp[$t->getState()->getName()][$t->getLetter()] = TRUE;
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
	 * @param  Interfaces\State|Interfaces\State[]
	 * @return void
	 * @throws Exceptions\InvalidArgumentException
	 */
	private function checkStates($states)
	{
		if (!is_array($states)) {
			$states = func_get_args();
		}

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
