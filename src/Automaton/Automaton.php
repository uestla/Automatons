<?php

/**
 * This file is part of the Automatons library
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/Automatons
 */


namespace Automaton;

require_once __DIR__ . '/exceptions.php';


/**
 * Creates and manipulates with (non-)deterministic automatons
 *
 * - removes epsilon transitions
 * - determinizes automaton
 * - minimizes automaton
 * - normalizes automaton
 * - compares two automatons
 *
 * @author Petr Kessler
 */
class Automaton
{
	/** @var array */
	protected $states = NULL;

	/** @var array */
	protected $alphabet = NULL;

	/** @var array */
	protected $transitions = NULL;

	/** @var array|string */
	protected $initials = NULL;

	/** @var array */
	protected $finals = NULL;

	/** @var bool */
	protected $hasEpsilon;



	/** @var bool */
	protected $isDeterministic = NULL;

	/** epsilon symbol */
	const EPSILON = '';



	/** @var Renderers\IRenderer */
	protected $renderer = NULL;



	/**
	 * States array structure:
	 *
	 * <code>
	 * array(
	 *	'state1' => array(
	 *		'symbol' => array('state2', 'state3')
	 *		or
	 *		'symbol' => 'state2'
	 *	)
	 * )
	 * </code>
	 *
	 * Empty string as a symbol means epsilon transition
	 *
	 * @param  array states array (see structure above)
	 * @param  array|string single initial state or non-empty set of initial states
	 * @param  array|string single final state or (possibly empty) set of final states
	 */
	function __construct(array $states, $initials, $finals)
	{
		$names = array_keys($states);
		$this->states = Helpers::valuesToKeys($names);
		if (!count($states)) {
			throw new InvalidStateSetException("Set of states cannot be empty.");
		}

		$initials = (array) $initials;
		if (!count($initials)) {
			throw new InvalidInitialsSetException("Initial state set cannot be empty.");
		}

		if (!Helpers::isSubsetOf($initials, $names)) {
			throw new StateNotFoundException("Initial state set is not a subset of state set.");
		}

		$finals = (array) $finals;
		if (!Helpers::isSubsetOf($finals, $names)) {
			throw new StateNotFoundException("Final state set is not a subset of state set.");
		}

		$this->initials = Helpers::valuesToKeys($initials);
		$this->finals = Helpers::valuesToKeys($finals);

		$this->transitions = array();
		foreach ($states as $state => $transitions) {
			if (!is_array($transitions)) {
				throw new InvalidInputException("State '$state' transitions - array expected, '" . gettype($transitions) . "' given.");
			}

			if ($this->alphabet === NULL) {
				$this->alphabet = Helpers::valuesToKeys(array_keys($transitions));
				unset($this->alphabet[static::EPSILON]);

				if (!count($this->alphabet)) {
					throw new InvalidAlphabetException("At least one symbol is required to be in alphabet.");
				}

				$this->hasEpsilon = isset($transitions[static::EPSILON]);

			} elseif (count($transitions) !== count($this->alphabet) + ($this->hasEpsilon ? 1 : 0)) {
				throw new InvalidTargetCount("Invalid target count - " . count($this->alphabet) . " expected, " . count($transitions) . " given.");
			}

			$this->transitions[(string) $state] = array();
			foreach ($transitions as $symbol => $targets) {
				if ($symbol !== static::EPSILON && !isset($this->alphabet[$symbol])) {
					throw new SymbolNotFoundException("Symbol '$symbol' not found in alphabet.");
				}

				$targets = (array) $targets;
				foreach ($targets as & $target) {
					if (!isset($this->states[$target])) {
						throw new StateNotFoundException("Target state '$target' not found in state set.");
					}

					$target = (string) $target;
				}

				$this->transitions[$state][$symbol] = array_unique($targets);
			}
		}
	}



	// === MANIPULATIONS ======================================================

	/** @return Automaton */
	function removeEpsilon()
	{
		if (!$this->hasEpsilon) {
			return $this;
		}

		$delta = array();
		foreach ($this->states as $state => $foo) {
			$delta[$state] = array();
			$eps = $this->epsilonClosure($state);
			unset($this->transitions[$state][static::EPSILON]);

			foreach ($this->alphabet as $symbol => $foo) {
				$delta[$state][$symbol] = array();
				foreach ($eps as $s) {
					if (isset($this->finals[$s])) {
						$this->finals[$state] = TRUE;
					}

					$delta[$state][$symbol] = array_merge($delta[$state][$symbol], $this->transitions[$s][$symbol]);
				}

				$delta[$state][$symbol] = array_unique($delta[$state][$symbol]);
			}
		}

		$this->hasEpsilon = FALSE;
		$this->transitions = $delta;
		$this->isDeterministic = NULL;

		return $this;
	}



	/**
	 * @param  string
	 * @return array
	 */
	function epsilonClosure($state)
	{
		if (!isset($this->states[$state])) {
			throw new StateNotFoundException("State '$state' not found in state set.");
		}

		$queue = array($state => TRUE);
		if ($this->hasEpsilon) {
			while (list($s, ) = each($queue)) {
				foreach ($this->transitions[$s][static::EPSILON] as $target) {
					$queue[$target] = TRUE;
				}
			}
		}

		return array_keys($queue);
	}



	/** @return Automaton */
	function determinize()
	{
		if ($this->isDeterministic()) {
			return $this;
		}

		$this->removeEpsilon();

		$initials = $states = array(Helpers::statesToString(array_keys($this->initials)) => TRUE);
		$finals = $delta = array();
		while (list($s, ) = each($states)) {
			$current = Helpers::stringToStates($s);
			sort($current);

			foreach ($current as $state) {
				if (isset($this->finals[$state])) {
					$finals[$s] = TRUE;
				}
			}

			$delta[$s] = array();
			foreach ($this->alphabet as $symbol => $foo) {
				$delta[$s][$symbol] = array();

				foreach ($current as $state) {
					$delta[$s][$symbol] = array_merge($delta[$s][$symbol], $this->transitions[$state][$symbol]);
				}

				$delta[$s][$symbol] = array_unique($delta[$s][$symbol]);
				sort($delta[$s][$symbol]);
				$delta[$s][$symbol] = array(Helpers::statesToString($delta[$s][$symbol]));

				$states[$delta[$s][$symbol][0]] = TRUE;
			}
		}

		$this->states = $states;
		$this->transitions = $delta;
		$this->initials = $initials;
		$this->finals = $finals;
		$this->isDeterministic = TRUE;

		return $this;
	}



	/** @return Automaton */
	function minimize()
	{
		$this->determinize();

		// split states into final and non-final group
		$transGroups['2'] = $transGroups['1'] = $transGroups = array();
		foreach ($this->states as $state => $foo) {
			$group = isset($this->finals[$state]) ? '2' : '1';
			$transGroups[$group][$state] = array();

			foreach ($this->alphabet as $symbol => $foo) {
				$target = reset($this->transitions[$state][$symbol]);
				$transGroups[$group][$state][$symbol] = isset($this->finals[$target]) ? '2' : '1';
			}
		}

		while (TRUE) {
			$i = 0;
			$newTrans = $groupMap = array();

			foreach ($transGroups as $states) {
				$names = array(); // new groups created by splitting the actual one

				foreach ($states as $state => $transitions) {
					$tmp = NULL; // name of group containing $transitions
					foreach ($names as $name) {
						if (in_array($transitions, $newTrans[$name], TRUE)) {
							$tmp = $name;
							break;
						}
					}

					if ($tmp === NULL) { // not found, create new one
						$newTrans[$tmp = $names[] = (string) ++$i] = array();
					}

					$newTrans[$tmp][$state] = $transitions;
					$groupMap[$state] = $tmp;
				}
			}

			foreach ($newTrans as $group => $states) {
				foreach ($states as $state => $transitions) {
					foreach ($transitions as $symbol => $foo) {
						$newTrans[$group][$state][$symbol] = $groupMap[reset($this->transitions[$state][$symbol])];
					}
				}
			}

			if ($newTrans === $transGroups) { // no more splitting done
				break;
			}

			$transGroups = $newTrans;
		}

		$this->states = Helpers::valuesToKeys($groupMap);

		$initials = $finals = array();
		foreach ($groupMap as $state => $group) {
			isset($this->initials[$state]) && ($initials[$group] = TRUE);
			isset($this->finals[$state]) && ($finals[$group] = TRUE);
		}

		$this->initials = $initials;
		$this->finals = $finals;

		$delta = array();
		foreach ($transGroups as $group => $states) {
			$delta[$group] = array();
			foreach ($states as $state => $transitions) {
				foreach ($transitions as $symbol => $target) {
					$delta[$group][$symbol] = array($target);
				}

				break; // only first state - the rest is the same
			}
		}

		$this->transitions = $delta;

		return $this;
	}



	/** @return Automaton */
	function normalize()
	{
		$this->minimize();

		$alphabet = $this->alphabet;
		ksort($alphabet);
		$i = '1';
		$map = array(reset($this->initials) => $i);
		$stateCount = count($this->states);

		$queue = $this->initials;
		while (list($state, ) = each($queue)) {
			foreach ($alphabet as $symbol => $foo) {
				$target = reset($this->transitions[$state][$symbol]);
				if (!isset($map[$target])) {
					$map[$target] = (string) ++$i;
					if ($i === $stateCount) { // all states mapped
						break 2;
					}
				}

				$queue[$target] = TRUE;
			}
		}

		$this->states = Helpers::valuesToKeys($map);

		$delta = array();
		foreach ($this->transitions as $state => $transitions) {
			$delta[$map[$state]] = array();
			foreach ($transitions as $symbol => $targets) {
				$delta[$map[$state]][$symbol] = array($map[reset($targets)]);
			}
		}

		ksort($delta);
		$this->transitions = $delta;

		$initials = $finals = array();
		foreach ($this->initials as $state => $foo) {
			$initials[$map[$state]] = TRUE;
		}

		foreach ($this->finals as $state => $foo) {
			$finals[$map[$state]] = TRUE;
		}

		$this->initials = $initials;
		$this->finals = $finals;

		return $this;
	}



	// === OPERATIONS ======================================================

	/**
	 * @param  Automaton
	 * @return bool
	 */
	function equals(Automaton $a)
	{
		return Helpers::compare($a, $this);
	}



	/** @return Automaton */
	function getComplement()
	{
		$a = clone $this;
		$a->determinize();

		return new Automaton($a->getTransitions(), $a->getInitials(), array_diff($a->getStates(), $a->getFinals()));
	}



	/**
	 * TODO: multiple symbol reading possibilities:
	 * - imagine having alphabet {a, aa, b}
	 * - then reading string 'aaa' could be made in 3 ways:
	 *		1) read 'a', read 'a', read 'a'
	 *		2) read 'a', read 'aa'
	 *		3) read 'aa', read 'a'
	 * - in that case input testing has to be done in multiple "threads"
	 * - multiple configuration sets have to be reflected
	 *
	 * @param  string
	 * @return bool
	 */
	function testInput($input)
	{
		$currents = $this->initials;

		while (strlen($input)) {
			$eps = array();
			foreach ($currents as $state => $foo) {
				$eps[$state] = $this->epsilonClosure($state);
			}

			// find the symbol from alphabet to read
			foreach ($this->alphabet as $symbol => $foo) {
				if (substr($input, 0, strlen($symbol)) === $symbol) {
					$next = array();
					foreach ($currents as $state => $foo) {
						$next = array_merge($next, $this->transitions[$state][$symbol]);
					}

					$input = substr($input, strlen($symbol));
					$currents = Helpers::valuesToKeys($next);
					continue 2; // read next symbol
				}
			}

			return FALSE; // no symbol from alphabet found
		}

		// is at least in one final state?
		foreach ($currents as $state => $foo) {
			if (isset($this->finals[$state])) {
				return TRUE;
			}
		}

		return FALSE;
	}



	// === GETTERS ======================================================

	/** @return array */
	function getStates()
	{
		return array_keys($this->states);
	}



	/** @return array */
	function getInitials()
	{
		return array_keys($this->initials);
	}



	/**
	 * @param  string
	 * @return bool
	 */
	function isInitialState($state)
	{
		if (!isset($this->states[$state])) {
			throw new StateNotFoundException("State '$state' not found.");
		}

		return isset($this->initials[$state]);
	}



	/** @return array */
	function getFinals()
	{
		return array_keys($this->finals);
	}



	/**
	 * @param  string
	 * @return bool
	 */
	function isFinalState($state)
	{
		if (!isset($this->states[$state])) {
			throw new StateNotFoundException("State '$state' not found.");
		}

		return isset($this->finals[$state]);
	}



	/** @return array */
	function getAlphabet()
	{
		return array_keys($this->alphabet);
	}



	/** @return bool */
	function hasEpsilon()
	{
		return $this->hasEpsilon;
	}



	/** @return array */
	function getTransitions()
	{
		return $this->transitions;
	}



	/** @return bool */
	function isDeterministic()
	{
		if ($this->isDeterministic === NULL) {
			$this->isDeterministic = $this->discoverDeterminism();
		}

		return $this->isDeterministic;
	}



	/** @return bool */
	protected function discoverDeterminism()
	{
		if ($this->hasEpsilon || count($this->initials) > 1) {
			return FALSE;
		}

		$reachable = array();
		foreach ($this->transitions as $state => $transitions) {
			isset($this->initials[$state]) && $reachable[$state] = TRUE;

			foreach ($transitions as $targets) {
				if (count($targets) !== 1) {
					return FALSE;
				}

				$reachable[reset($targets)] = TRUE;
			}
		}

		return count($reachable) === count($this->states);
	}



	// === RENDERING ======================================================

	/**
	 * @param  Renderers\IRenderer
	 * @return Automaton
	 */
	function setRenderer(Renderers\IRenderer $renderer)
	{
		$this->renderer = $renderer;
		return $this;
	}



	/** @return Renderers\IRenderer */
	function getRenderer()
	{
		return $this->renderer === NULL ? ($this->renderer = new Renderers\TextRenderer) : $this->renderer;
	}



	/** @return Automaton */
	function render()
	{
		$this->getRenderer()->render($this);
		return $this;
	}



	/** @return string */
	function __toString()
	{
		ob_start();
		$this->render();

		return ob_get_clean();
	}
}
