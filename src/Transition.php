<?php

namespace Automaton;


class Transition implements Interfaces\Transition
{
	/** @var Interfaces\State */
	private $state;

	/** @var string */
	private $letter;

	/** @var Interfaces\State[] */
	private $target;



	/**
	 * @param Interfaces\State
	 * @param transmition letter
	 * @param Interfaces\State[] target states
	 */
	function __construct(Interfaces\State $state, $letter, array $target = NULL)
	{
		$this->state = $state;
		$this->letter = $letter;
		$this->target = $target;
	}



	/**
	 * @return Interfaces\State[] target states
	 */
	function getTarget()
	{
		return $this->states;
	}
}
