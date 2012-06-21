<?php

namespace Automaton;

require_once __DIR__ . '/../Automaton.php';


interface IRenderer
{
	/** @param Automaton */
	function render(Automaton $automaton);
}
