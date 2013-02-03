<?php

namespace Automaton\Renderers;

use Automaton\Automaton;


interface IRenderer
{
	/**
	 * @param  Automaton
	 * @return void
	 */
	function render(Automaton $a);
}
