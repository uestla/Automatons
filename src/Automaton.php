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



	function __construct(array $states, array $initials, array $finals)
	{
		$this->states = array();
		$alphabet = NULL;

		foreach ($states as $name => $transitions) {
			if ($alphabet === NULL) {
				$alphabet = array_keys($transitions);

			} elseif (array_keys($transitions) !== $alphabet) {
				throw new InvalidStateException("Alphabet has to be the same for all transition.");
			}

			foreach ($transitions as $letter => $targets) {
				foreach ($targets as $state) {
					if (!isset($states[$state])) {
						throw new InvalidStateException("State '{$state}' not found.");
					}
				}

				$transitions[$letter] = array_unique($transitions[$letter]);
				sort($transitions[$letter]);
			}

			$this->states[$name] = $transitions;
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
}



class InvalidStateException extends Exception {}
