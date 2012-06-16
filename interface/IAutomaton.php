<?php

interface IAutomaton
{
	/** @return IAutomaton */
	function determinize();

	/** @return IAutomaton */
	function minimize();
}