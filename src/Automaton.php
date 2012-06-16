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
	 * @param Interfaces\Transition[] transition function
	 * @param Interfaces\State[] initial states
	 * @param Interfaces\State[] final states
	 */
	function __construct(array $states, array $alphabet, array $transitions, array $inits, array $finals)
	{
		$this->setStates($states);
		$this->alphabet = array_unique($alphabet);
		sort($this->alphabet);
		$this->setTransitions($transitions);
		$this->setInits($inits);
		$this->setFinals($finals);
	}



	/** @param Interfaces\State[] state list */
	function setStates(array $states)
	{
		$this->states = array();

		foreach ($states as $state) {
			$this->states[$state->getName()] = $state;
		}

		return $this;
	}



	/**
	 * Checks if:
	 * - all states exist (even in the target array)
	 * - all letters exist
	 * - each state has its transition for each letter
	 * - there are no multiple transitions for certain state and letter
	 *
	 * @param  Interfaces\Transition[]
	 * @return Interfaces\Automaton
	 * @throws Exceptions\InvalidArgumentException
	 */
	function setTransitions(array $transitions)
	{
		// [ STATE_NAME => [ LETTER => TRUE, LETTER => TRUE, ... ], ... ]
		$tmp = array();

		foreach ($transitions as $t) {
			$this->checkStates($t->getState());

			if (!in_array($t->getLetter(), $this->alphabet, TRUE)) {
				throw new Exceptions\InvalidArgumentException("Letter '{$t->getLetter()}' not found in the alphabet.");
			}

			if (!isset($tmp[$t->getState()->getName()])) {
				$tmp[$t->getState()->getName()] = array();

			} elseif (isset($tmp[$t->getState()->getName()][$t->getLetter()])) {
				throw new Exceptions\InvalidArgumentException("Multiple transition from state '{$t->getState()->getName()}' for letter '{$t->getLetter()}'.");
			}

			$tmp[$t->getState()->getName()][$t->getLetter()] = TRUE;
			$this->checkStates($t->getTarget());
		}

		if (count($tmp) !== count($this->states)) {
			throw new Exceptions\InvalidArgumentException("Transition for some states weren't specified.");
		}

		foreach ($tmp as $name => $target) {
			if (count($target) !== count($this->alphabet)) {
				throw new Exceptions\InvalidArgumentException("Missing transition from state '{$name}' for some letter(s).");
			}
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
		if (!count($inits)) {
			throw new Exceptions\InvalidArgumentException("At least 1 initial state required.");
		}

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
		if (!count($inits)) {
			throw new Exceptions\InvalidArgumentException("At least 1 final state required.");
		}

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
			if (!isset($this->states[$state->getName()])) {
				throw new Exceptions\InvalidArgumentException("State '{$state->getName()}' not found in the state array.");
			}
		}
	}



	/** @return Interfaces\Automaton provides fluent interface */
	function determinize()
	{
		return $this;
	}



	/** @return Interfaces\Automaton provides fluent interface */
	function minimize()
	{
		return $this;
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
		if (!isset($this->states[$name])) {
			throw new Exceptions\StateNotFoundException("State '$name' not found.");
		}

		return $this->states[$name];
	}



	/**
	 * State existence tester
	 *
	 * @param  string state name
	 * @return bool
	 */
	function __isset($name)
	{
		return isset($this->states[$name]);
	}
}
