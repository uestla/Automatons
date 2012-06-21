<?php

namespace Automaton;

require_once __DIR__ . '/renderers/DefaultRenderer.php';
require_once __DIR__ . '/exceptions/exceptions.php';


/**
 * States
 * ------
 *  |- A
 *  |  |- a
 *  |  |  `- B
 *  |  |- b
 *  |  |- c
 *  |  |  |- C
 *  |  |  `- A
 *  |  `- eps
 *  |- B
 *  |  |- a
 *  |  |  `- A
 *  |  |- b
 *  |  |  `- A
 *  |  |- c
 *  |  |  |- A
 *  |  |  `- B
 *  |  `- eps
 *  |     `- A
 *  `- C
 *     |- a
 *     |  `- C
 *     |- b
 *     |  `- C
 *     |- c
 *     |  `- C
 *     `- eps
 */
class Automaton
{
	/** @var array */
	private $states = NULL;

	/** @var array */
	private $initials = NULL;

	/** @var array */
	private $finals = NULL;

	/** @var array */
	private $alphabet = NULL;

	/** @var IRenderer */
	private $renderer = NULL;



	/**
	 * @param  array
	 * @param  array
	 * @param  array
	 */
	function __construct(array $states, array $initials, array $finals)
	{
		if (!count($states) || !count($initials) || !count($finals)) {
			throw new InvalidStateException("At least one state, one initial and one final state required.");
		}

		$this->states = array();

		foreach ($states as $state => $transitions) {
			if ($this->alphabet === NULL) {
				$this->alphabet = array();
				$letters = array_keys($transitions);
				foreach ($letters as $letter) {
					$this->alphabet[$letter] = TRUE;
				}

				if (!count($this->alphabet) || $this->alphabet === array('' => TRUE)) {
					throw new InvalidStateException("At least one non-epsilon letter required in the alphabet.");
				}

				ksort($this->alphabet);

			} elseif (count(array_diff(array_keys($transitions), $letters))) {
				throw new InvalidStateException("Alphabet has to be the same for all transition.");
			}

			$ts = array();
			foreach ($transitions as $letter => $targets) {
				$ts[$letter] = array();
				foreach ($targets as $target) {
					if (!isset($states[$target])) {
						throw new InvalidStateException("State '{$target}' not found.");
					}

					$ts[$letter][$target] = TRUE;
				}

				ksort($ts[$letter]);
			}

			ksort($ts);
			$this->states[$state] = $ts;
		}

		$is = array();
		foreach ($initials as $state) {
			if (!isset($this->states[$state])) {
				throw new InvalidStateException("State '{$state}' not found.");
			}

			$is[$state] = TRUE;
		}

		$fs = array();
		foreach ($finals as $state) {
			if (!isset($this->states[$state])) {
				throw new InvalidStateException("State '{$state}' not found.");
			}

			$fs[$state] = TRUE;
		}

		ksort($this->states);
		ksort($is);
		ksort($fs);

		$this->initials = $is;
		$this->finals = $fs;
	}



	/** @return Automaton provides fluent interface */
	function determinize()
	{
		if (isset($this->alphabet[''])) {
			$this->removeEpsilon();
		}

		$states = $initials = $finals = $queue = array();

		$initials[ $initname = static::generateStateName(array_keys($this->initials)) ] = TRUE;
		$queue[ $initname ] = $this->initials;

		while (list($name, $ss) = each($queue)) {
			$states[$name] = array();

			foreach ($this->alphabet as $letter => $foo) {
				$ts = array();
				$final = FALSE;

				foreach ($ss as $state => $foo) {
					isset($this->finals[$state]) && ($final = TRUE);

					foreach ($this->states[$state][$letter] as $target => $foo) {
						$ts[$target] = TRUE;
					}
				}

				ksort($ts);

				$queue[ $tsname = static::generateStateName(array_keys($ts)) ] = $ts;
				$final && $finals[$name] = TRUE;
				$states[$name][$letter] = array($tsname => TRUE);
			}
		}

		ksort($states);
		ksort($finals);

		$this->states = $states;
		$this->initials = $initials;
		$this->finals = $finals;
		return $this;
	}



	/** @return Automaton provides fluent interface */
	function removeEpsilon()
	{
		if (!isset($this->alphabet[''])) {
			throw new InvalidStateException("Epsilon not found in the alphabet.");
		}

		foreach ($this->states as & $transitions) {
			$queue = $transitions[''];

			while (list($state, ) = each($queue)) {
				foreach ($this->states[$state] as $letter => $targets) {
					if ($letter === '') {
						foreach ($targets as $target => $foo) {
							$queue[$target] = TRUE;
						}

						continue;
					}

					foreach ($targets as $target => $foo) {
						$transitions[$letter][$target] = TRUE;
					}

					ksort($transitions[$letter]);
				}
			}

			unset($transitions['']);
		}

		unset($this->alphabet['']);
		return $this;
	}



	/** @return Automaton provides fluent interface */
	function minimize()
	{
		$groups['II'] = $groups['I'] = $groups = array();
		foreach ($this->states as $state => $t) {
			$group = isset($this->finals[$state]) ? 'II' : 'I';
			$groups[ $group ][] = $state;
			sort($groups[$group]);
		}

		while (TRUE) {
			$max = 0;
			$newg = array();
			$matrix = $this->createStateGroupsMatrix($groups);

			foreach ($matrix as $group => $states) {
				asort($states);
				$processed = array();

				foreach ($states as $state => $targets) {
					if (!isset($processed[$targets])) {
						$newg[ $name = str_repeat('I', ++$max) ] = array();
						$processed[$targets] = TRUE;
					}

					$newg[$name][] = $state;
					sort($newg[$name]);
				}
			}

			if ($groups === $newg) {
				break;
			}

			$groups = $newg;
		}

		$stategroups = $this->createStateGroups($groups);
		$states = $initials = $finals = array();
		foreach ($this->states as $state => $transitions) {
			if (isset($this->initials[$state])) {
				$initials[$stategroups[$state]] = TRUE;
			}

			if (isset($this->finals[$state])) {
				$finals[$stategroups[$state]] = TRUE;
			}

			$name = $stategroups[$state];
			if (isset($states[$name])) {
				continue;
			}

			$states[$name] = array();
			foreach ($transitions as $letter => $targets) {
				$states[$name][$letter] = array();
				foreach ($targets as $target => $foo) {
					$states[$name][$letter][$stategroups[$target]] = $foo;
				}
			}
		}

		ksort($states);
		ksort($initials);
		ksort($finals);

		$this->states = $states;
		$this->initials = $initials;
		$this->finals = $finals;

		return $this;
	}



	private function createStateGroupsMatrix(array $groups)
	{
		$matrix = array();
		$stategroups = $this->createStateGroups($groups);

		foreach ($groups as $group => $states) {
			$matrix[$group] = array();
			foreach ($states as $state) {
				$matrix[$group][$state] = array();
				foreach ($this->states[$state] as $letter => $targets) {
					$matrix[$group][$state][$letter] = array();
					if (count($targets) !== 1) {
						throw new InvalidStateException("Automaton not determinized.");
					}

					list($target, ) = each($targets);
					$matrix[$group][$state][$letter] = $stategroups[$target];
				}

				$matrix[$group][$state] = implode('|', $matrix[$group][$state]);
			}
		}

		return $matrix;
	}



	private function createStateGroups(array $groups)
	{
		$stategroups = array();
		foreach ($groups as $group => $states) {
			foreach ($states as $state) {
				$stategroups[$state] = $group;
			}
		}

		return $stategroups;
	}



	/** @return Automaton provides fluent interface */
	function normalize()
	{
		$max = 0;
		$names = array();
		$list = $this->initials;

		while (list($state, ) = each($list)) {
			!isset($names[$state]) && ($names[$state] = ++$max);

			foreach ($this->states[$state] as $targets) {
				foreach ($targets as $target => $foo) {
					if (!isset($names[$target])) {
						$names[$target] = ++$max;
						$list[$target] = TRUE;
					}
				}
			}
		}

		foreach ($this->states as $state => $transitions) {
			foreach ($transitions as & $targets) {
				foreach ($targets as $target => $foo) {
					unset($targets[$target]);
					$targets[$names[$target]] = $foo;
				}
			}

			unset($this->states[$state]);
			$this->states[$names[$state]] = $transitions;
		}

		foreach ($this->initials as $state => $foo) {
			unset($this->initials[$state]);
			$this->initials[$names[$state]] = $foo;
		}

		foreach ($this->finals as $state => $foo) {
			unset($this->finals[$state]);
			$this->finals[$names[$state]] = $foo;
		}

		ksort($this->states);
		ksort($this->initials);
		ksort($this->finals);

		return $this;
	}



	static function generateStateName(array $list)
	{
		return '{' . implode(',', $list) . '}';
	}



	function getStates()
	{
		return $this->states;
	}



	function isInitialState($state)
	{
		return isset($this->initials[$state]);
	}



	function isFinalState($state)
	{
		return isset($this->finals[$state]);
	}



	function setRenderer(IRenderer $renderer)
	{
		$this->renderer = $renderer;
		return $this;
	}



	function getRenderer()
	{
		return $this->renderer === NULL ? ($this->renderer = new DefaultRenderer()) : $this->renderer;
	}



	function __toString()
	{
		ob_start();
		$this->getRenderer()->render($this);
		return ob_get_clean();
	}
}
