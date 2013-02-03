<?php

/**
 * This file is part of the Automatons library
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/Automatons
 */


namespace Automaton\Renderers;

use Automaton\Automaton;


/**
 * Interface for automaton rendering
 *
 * @author Petr Kessler
 */
interface IRenderer
{
	/**
	 * @param  Automaton
	 * @return void
	 */
	function render(Automaton $a);
}
