<?php

namespace Automaton;


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



	function __construct(array $states, array $initials, array $finals)
	{
		if (!count($states) || !count($initials) || !count($finals)) {
			throw new InvalidStateException("At least one state, one initial and one final state required.");
		}

		$this->states = array();

		foreach ($states as $state => $transitions) {
			if ($this->alphabet === NULL) {
				$this->alphabet = array_keys($transitions);
				if (!count($this->alphabet) || $this->alphabet === array('')) {
					throw new InvalidStateException("At least one non-epsilon letter required in the alphabet.");
				}

				sort($this->alphabet);

			} elseif (count(array_diff(array_keys($transitions), $this->alphabet))) {
				throw new InvalidStateException("Alphabet has to be the same for all transition.");
			}

			foreach ($transitions as $letter => $targets) {
				foreach ($targets as $target) {
					if (!isset($states[$target])) {
						throw new InvalidStateException("State '{$target}' not found.");
					}
				}

				$transitions[$letter] = array_unique($transitions[$letter]);
				sort($transitions[$letter]);
			}

			ksort($transitions);
			$this->states[$state] = $transitions;
		}

		foreach ($initials as $state) {
			if (!isset($this->states[$state])) {
				throw new InvalidStateException("State '{$state}' not found.");
			}
		}

		foreach ($finals as $key => $state) {
			if (!isset($this->states[$state])) {
				throw new InvalidStateException("State '{$state}' not found.");
			}

			$finals[$state] = TRUE;
			unset($finals[$key]);
		}

		ksort($this->states);
		sort($initials);
		ksort($finals);

		$this->initials = array_unique($initials);
		$this->finals = $finals;
	}



	function determinize()
	{
		if (in_array('', $this->alphabet, TRUE)) {
			$this->removeEpsilon();
		}

		$states = $initials = $finals = $queue = array();
		$queue[] = $this->initials;

		while (list(, $ss) = each($queue)) {
			$states[ $name = $this->generateStateName($ss) ] = array();

			if (!count($initials)) {
				$initials[] = $name;
			}

			foreach ($this->alphabet as $letter) {
				$ts = array();
				$final = FALSE;

				foreach ($ss as $state) {
					if (!$final && $this->isFinalState($state)) {
						$final = TRUE;
					}

					foreach ($this->states[$state][$letter] as $target) {
						if (!in_array($target, $ts, TRUE)) {
							$ts[] = $target;
						}
					}
				}

				sort($ts);

				if (!in_array($ts, $queue, TRUE)) {
					$queue[] = $ts;
				}

				if ($final && !isset($finals[$name])) {
					$finals[$name] = TRUE;
				}

				$states[$name][$letter] = array($this->generateStateName($ts));
			}
		}

		ksort($states);
		sort($initials);
		ksort($finals);

		$this->states = $states;
		$this->initials = $initials;
		$this->finals = $finals;
		return $this;
	}



	function removeEpsilon()
	{
		if (!in_array('', $this->alphabet, TRUE)) {
			throw new InvalidStateException("Epsilon not found in the alphabet.");
		}

		foreach ($this->states as & $transitions) {
			$queue = $transitions[''];

			while (list(, $state) = each($queue)) {
				foreach ($this->states[$state] as $letter => $targets) {
					if ($letter === '') {
						foreach ($targets as $target) {
							if (!in_array($target, $queue, TRUE)) {
								$queue[] = $target;
							}
						}

						continue;
					}

					foreach ($targets as $target) {
						if (!in_array($target, $transitions[$letter], TRUE)) {
							$transitions[$letter][] = $target;
							sort($transitions[$letter]);
						}
					}
				}
			}

			unset($transitions['']);
		}

		unset($this->alphabet[array_search('', $this->alphabet, TRUE)]);
		return $this;
	}



	function minimize()
	{
		// TODO...
		return $this;
	}



	function normalize()
	{
		$max = 0;
		$names = array();
		$list = $this->initials;

		while (list(, $state) = each($list)) {
			if (!isset($names[$state])) {
				$names[$state] = ++$max;
			}

			foreach ($this->states[$state] as $targets) {
				foreach ($targets as $target) {
					if (!isset($names[$target])) {
						$names[$target] = ++$max;
						$list[] = $target;
					}
				}
			}
		}

		foreach ($this->states as $state => $transitions) {
			foreach ($transitions as & $targets) {
				foreach ($targets as $key => $target) {
					$targets[$key] = $names[$target];
				}
			}

			unset($this->states[$state]);
			$this->states[$names[$state]] = $transitions;
		}

		foreach ($this->initials as $key => $state) {
			$this->initials[$key] = $names[$state];
		}

		foreach ($this->finals as $state => $val) {
			unset($this->finals[$state]);
			$this->finals[$names[$state]] = $val;
		}

		ksort($this->states);
		sort($this->initials);
		ksort($this->finals);

		return $this;
	}



	function generateStateName(array $list)
	{
		return '{' . implode(',', $list) . '}';
	}



	function isFinalState($state)
	{
		return isset($this->finals[$state]);
	}
}



class InvalidStateException extends \Exception {}
