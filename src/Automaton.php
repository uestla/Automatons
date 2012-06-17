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

		foreach ($finals as $state) {
			if (!isset($this->states[$state])) {
				throw new InvalidStateException("State '{$state}' not found.");
			}
		}

		$this->initials = $initials;
		$this->finals = $finals;
	}



	function determinize()
	{
		if (in_array('', $this->alphabet, TRUE)) {
			$this->removeEpsilon();
		}
	}



	function removeEpsilon()
	{
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
	}
}



class InvalidStateException extends Exception {}
