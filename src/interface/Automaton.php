<?php

namespace Automaton\Interfaces;


interface IAutomaton
{
	/** @return IAutomaton */
	function determinize();

	/** @return IAutomaton */
	function minimize();
}
