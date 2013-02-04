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
 * Renders automaton as a textual table
 *
 * @author Petr Kessler
 */
class TextRenderer implements IRenderer
{
	const SYMBOL_INITIAL = '=>';
	const SYMBOL_FINAL = '<=';
	const SYMBOL_BOTH = '<>';
	const EPSILON = '\\eps';



	/**
	 * @param  Automaton
	 * @return void
	 */
	function render(Automaton $a)
	{
		// === COLUMN WIDTHS ======================
		$widths = array();
		$widths['states'] = static::getItemMaxLen($a->getStates()) + 5;
		$widths['symbols'] = array();

		foreach (static::getSymbols($a) as $symbol) {
			$names = array(static::getOutSymbol($symbol)); // symbol may be longer than the longest state name
			foreach ($a->getTransitions() as $transitions) {
				$names[] = static::statesToString($a, $transitions[$symbol]);
			}

			$widths['symbols'][$symbol] = static::getItemMaxLen($names) + 2;
		}


		// === HEADER ======================
		echo '+', str_repeat('-', $widths['states']), '+';
		foreach (static::getSymbols($a) as $symbol) {
			echo str_repeat('-', $widths['symbols'][$symbol]), '+';
		}

		echo "\n|", str_repeat(' ', $widths['states']), '|';
		foreach (static::getSymbols($a) as $symbol) {
			$out = static::getOutSymbol($symbol);
			$padding = static::getPadding($widths['symbols'][$symbol], $out);
			echo str_repeat(' ', ceil($padding)), $out, str_repeat(' ', floor($padding)), '|';
		}

		echo "\n+", str_repeat('=', $widths['states']), '+';
		foreach (static::getSymbols($a) as $symbol) {
			echo str_repeat('=', $widths['symbols'][$symbol]), '+';
		}


		// === BODY ======================
		foreach ($a->getTransitions() as $state => $transitions) {
			$out = ($a->isInitialState($state) && $a->isFinalState($state)
				? static::SYMBOL_BOTH . ' '
				: (
					$a->isInitialState($state)
						? static::SYMBOL_INITIAL . ' '
						: ($a->isFinalState($state) ? static::SYMBOL_FINAL . ' ' : '')
				))
				. $state;

			echo "\n|", str_repeat(' ', $widths['states'] - strlen($out) - 1), $out, ' |';

			foreach ($transitions as $symbol => $targets) {
				$out = static::statesToString($a, $targets);
				$padding = static::getPadding($widths['symbols'][$symbol], $out);
				echo str_repeat(' ', ceil($padding)), $out, str_repeat(' ', floor($padding)), '|';
			}
		}


		// === FOOTER ======================
		echo "\n+", str_repeat('-', $widths['states']), '+';
		foreach (static::getSymbols($a) as $symbol) {
			echo str_repeat('-', $widths['symbols'][$symbol]), '+';
		}

		echo "\n";
	}



	/**
	 * @param  string
	 * @return string
	 */
	protected static function getOutSymbol($symbol)
	{
		return $symbol === Automaton::EPSILON ? static::EPSILON : $symbol;
	}



	/**
	 * @param  array
	 * @return int or NULL if empty array
	 */
	protected static function getItemMaxLen(array $a)
	{
		$max = NULL;
		foreach ($a as $item) {
			$len = strlen($item);
			($max === NULL || $len > $max) && ($max = $len);
		}

		return $max;
	}



	/**
	 * @param  Automaton
	 * @param  array
	 * @return string
	 */
	protected static function statesToString(Automaton $automaton, array $states)
	{
		return $automaton->isDeterministic() && count($states) === 1
				? (string) reset($states)
				: '{' . implode(', ', $states) . '}';
	}



	/**
	 * @param  int
	 * @param  string
	 * @return float
	 */
	protected static function getPadding($width, $s)
	{
		return ($width - strlen($s)) / 2;
	}



	/**
	 * @param  Automaton
	 * @return array
	 */
	protected static function getSymbols(Automaton $a)
	{
		$symbols = $a->getAlphabet();
		$a->hasEpsilon() && ($symbols[] = Automaton::EPSILON);
		return $symbols;
	}
}
