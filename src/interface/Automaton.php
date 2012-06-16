<?php

namespace Automaton\Interfaces;


interface Automaton
{
	/** @return Automaton */
	function determinize();

	/** @return Automaton */
	function minimize();
}
