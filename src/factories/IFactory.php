<?php

namespace Automaton;


interface IFactory
{
	/** @return Automaton */
	function create();
}
