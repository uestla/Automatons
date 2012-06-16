<?php

namespace Automaton\Interfaces;


interface State
{
	/** @return string */
	function getName();

	/** @return string */
	function __toString();
}
