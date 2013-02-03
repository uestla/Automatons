<?php

namespace Automaton;

require_once __DIR__ . '/exceptions.php';


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
	protected $isDeterministic;

	/** epsilon symbol */
	const EPSILON = '';



	/** @var Renderers\IRenderer */
	protected $renderer = NULL;



	// === MANIPULATIONS ======================================================

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
	 * @param  array (possibly empty) set of final states
	 */
	function __construct(array $states, $initials, array $finals)
	{
		$names = array_keys($states);
		$this->states = Helpers::valuesToKeys($names);
		if (!count($states)) {
			throw new InvalidStateSetException("Set of states cannot be empty.");
		}

		$initials = (array) $initials;
		if (!Helpers::isSubsetOf($initials, $names)) {
			throw new StateNotFoundException("Initial state set is not a subset of state set.");
		}

		if (!Helpers::isSubsetOf($finals, $names)) {
			throw new StateNotFoundException("Final state set is not a subset of state set.");
		}

		$this->initials = Helpers::valuesToKeys($initials);
		$this->finals = Helpers::valuesToKeys($finals);

		$this->transitions = array();
		foreach ($states as $state => $transitions) {
			if ($this->alphabet === NULL) {
				$this->alphabet = Helpers::valuesToKeys(array_keys($transitions));
				if (!count($this->alphabet) || $this->alphabet === array(static::EPSILON => TRUE)) {
					throw new InvalidAlphabetException("Alphabet is empty or contains epsilon symbol only.");
				}

			} elseif (count($transitions) !== count($this->alphabet)) {
				throw new InvalidTargetCount("Invalid target count - " . count($this->alphabet) . " expected, " . count($transitions) . " given.");
			}

			$this->transitions[(string) $state] = array();
			foreach ($transitions as $symbol => $targets) {
				if (!isset($this->alphabet[$symbol])) {
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

		$this->discoverDeterminism();
	}



	/** @return Automaton */
	function removeEpsilon()
	{
		if (!isset($this->alphabet[static::EPSILON])) {
			return $this;
		}

		$delta = array();
		foreach ($this->states as $state => $foo) {
			$delta[$state] = array();
			$eps = $this->epsilonClosure($state);

			foreach ($this->alphabet as $symbol => $foo) {
				if ($symbol === static::EPSILON) {
					unset($this->transitions[$state][$symbol]);
					continue;
				}

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

		unset($this->alphabet[static::EPSILON]);
		$this->transitions = $delta;
		$this->discoverDeterminism();

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
		if (isset($this->alphabet[static::EPSILON])) {
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
		if ($this->isDeterministic) {
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



	/** @return void */
	protected function discoverDeterminism()
	{
		if (isset($this->alphabet[static::EPSILON])) {
			$this->isDeterministic = FALSE;
			return ;
		}

		foreach ($this->transitions as $transitions) {
			foreach ($transitions as $targets) {
				if (count($targets) > 1) {
					$this->isDeterministic = FALSE;
					return ;
				}
			}
		}

		$this->isDeterministic = TRUE;
	}



	/** @return Automaton */
	function minimize()
	{
		$this->determinize();

		$transGroups['II'] = $transGroups['I'] = $transGroups = array();
		foreach ($this->states as $state => $foo) {
			$group = isset($this->finals[$state]) ? 'II' : 'I';
			$transGroups[$group][$state] = array();

			foreach ($this->alphabet as $symbol => $foo) {
				$target = reset($this->transitions[$state][$symbol]);
				$transGroups[$group][$state][$symbol] = isset($this->finals[$target]) ? 'II' : 'I';
			}
		}

		while (TRUE) {
			$i = 0;
			$newTrans = $groupMap = array();

			foreach ($transGroups as $states) {
				$name = str_repeat('I', ++$i);
				$newTrans[$name] = array();
				foreach ($states as $state => $transitions) {
					if (reset($newTrans[$name]) !== FALSE && !in_array($transitions, $newTrans[$name], TRUE)) {
						$name = str_repeat('I', ++$i);
						$newTrans[$name] = array();
					}

					$newTrans[$name][$state] = $transitions;
					$groupMap[$state] = $name;
				}
			}

			foreach ($newTrans as $group => $states) {
				foreach ($states as $state => $transitions) {
					foreach ($transitions as $symbol => $foo) {
						$newTrans[$group][$state][$symbol] = $groupMap[reset($this->transitions[$state][$symbol])];
					}
				}
			}

			if ($newTrans === $transGroups) {
				break;
			}

			$transGroups = $newTrans;
		}

		$this->states = Helpers::valuesToKeys($groupMap);
		$initials = $finals = array();
		foreach ($groupMap as $state => $group) {
			if (isset($this->initials[$state])) {
				$initials[$group] = TRUE;
			}

			if (isset($this->finals[$state])) {
				$finals[$group] = TRUE;
			}
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
		return isset($this->finals[$state]);
	}



	/** @return array */
	function getAlphabet()
	{
		return array_keys($this->alphabet);
	}



	/** @return array */
	function getTransitions()
	{
		return $this->transitions;
	}



	/** @return bool */
	function isDeterministic()
	{
		return $this->isDeterministic;
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
