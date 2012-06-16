<?php

namespace Automaton\Interfaces;


interface IState
{
	/** @return string */
	function getName();

	/** @return bool */
	function isInitial();

	/** @return bool */
	function isFinal();
}
