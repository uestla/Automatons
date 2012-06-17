<?php

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
		$this->states = array();

		foreach ($states as $state => $transitions) {
			if ($this->alphabet === NULL) {
				$this->alphabet = array_keys($transitions);

			} elseif (array_keys($transitions) !== $this->alphabet) {
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

		ksort($states);
		sort($initials);
		ksort($finals);

		$this->initials = $initials;
		$this->finals = $finals;
	}



	function determinize()
	{
		if (in_array('', $this->alphabet, TRUE)) {
			$this->removeEpsilon();
		}

		$states = $initials = $finals = $queue = array();
		$queue[] = $this->initials;

		while (list(, $targets) = each($queue)) {
			$states[ $name = $this->generateStateName($targets) ] = array();

			if (!count($initials)) {
				$initials[] = $name;
			}

			foreach ($this->alphabet as $letter) {
				$ts = array();
				$final = FALSE;

				foreach ($targets as $state) {
					if (!$final && isset($this->finals[$state])) {
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

				$states[$name][$letter] = $this->generateStateName($ts);
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



	function generateStateName(array $list)
	{
		return '{' . implode(',', $list) . '}';
	}
}



class InvalidStateException extends Exception {}
