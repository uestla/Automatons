<?php

namespace Automaton;


class Automaton implements Interfaces\Automaton, \ArrayAccess
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
		$this->states = array_unique($states);
		$this->alphabet = array_unique($alphabet);
		sort($this->alphabet);
		$this->setTransitions($transitions);
		$this->setInits($inits);
		$this->setFinals($finals);
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
		if (!count($finals)) {
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
			if (!isset($this[$state->getName()])) {
				throw new Exceptions\InvalidArgumentException("State '{$state->getName()}' not found in the state array.");
			}
		}
	}



	/********************************** manipulations **********************************/



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



	/********************************** ArrayAccess interface **********************************/



	/**
	 * @param  string state name
	 * @return bool
	 */
	function offsetExists($name)
	{
		return $this->getState($name, FALSE) !== NULL;
	}



	/**
	 * @param  string state name
	 * @return Interfaces\State|NULL
	 */
	function offsetGet($name)
	{
		return $this->getState($name);
	}



	/** @throws Exceptions\PermissionDeniedException */
	function offsetSet($name, $state) {
		throw new Exceptions\PermissionDeniedException;
	}



	/** @throws Exceptions\PermissionDeniedException */
	function offsetUnset($name)
	{
		throw new Exceptions\PermissionDeniedException;
	}



	/**
	 * State getter
	 *
	 * @param  string state name
	 * @param  bool needed?
	 * @return Interfaces\State|NULL
	 * @throws Exceptions\StateNotFoundException
	 */
	private function getState($name, $need = TRUE)
	{
		foreach ($this->states as $state) {
			if ($state->getName() === $name) {
				return $state;
			}
		}

		if ($need) {
			throw new Exceptions\StateNotFoundException("State '{$name}' not found.");
		}

		return NULL;
	}
}
