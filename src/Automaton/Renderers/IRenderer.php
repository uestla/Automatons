<?php

/**
 * This file is part of the Automaton package
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/Automatons
 */


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
