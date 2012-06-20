<?php

namespace Automaton;


interface IRenderer
{
	/** @param Automaton */
	function render(Automaton $automaton);
}
