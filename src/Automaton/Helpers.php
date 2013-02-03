<?php

/**
 * This file is part of the Automatons library
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/Automatons
 */


namespace Automaton;


abstract class Helpers
{
	/**
	 * Turns array('one', 'two') into array('one' => TRUE, 'two' => TRUE)
	 * for faster item existence testing (just type isset($array['one'])
	 * instead of in_array('one', $array, TRUE))
	 *
	 * @param  array
	 * @return array
	 */
	static function valuesToKeys(array $a)
	{
		$r = array();
		foreach ($a as $item) {
			$r[(string) $item] = TRUE;
		}

		return $r;
	}



	/**
	 * Tests if values in $subset is contained by $set as well (does not check keys)
	 *
	 * @param  array
	 * @param  array
	 * @return bool
	 */
	static function isSubsetOf(array $subset, array $set)
	{
		$set = static::valuesToKeys($set);
		foreach ($subset as $item) {
			if (!isset($set[$item])) {
				return FALSE;
			}
		}

		return TRUE;
	}



	/**
	 * @param  array
	 * @return string
	 */
	static function statesToString(array $states)
	{
		return '[' . implode(', ', $states) . ']';
	}



	/**
	 * @param  string
	 * @return array
	 */
	static function stringToStates($s)
	{
		if (substr($s, 0, 1) !== '[' || substr($s, -1) !== ']') {
			throw new InvalidInputException("Invalid string format.");
		}

		return explode(', ', substr($s, 1, -1));
	}
}
