<?php

namespace Automaton;

require_once __DIR__ . '/IRenderer.php';
require_once __DIR__ . '/../Automaton.php';


class DefaultRenderer implements IRenderer
{
	const EPSILON = '\\eps';
	const INITIAL_S = '>';
	const FINAL_S = '<';
	const STATE_SEPARATOR = '|';
	const NO_TARGET = '-';



	/** @param  Automaton */
	function render(Automaton $a)
	{
		$cell = 2;
		$matrix = array();

		foreach ($a->getStates() as $state => $transitions) {
			$matrix[$state] = array();

			foreach ($transitions as $letter => $targets) {
				$letter === '' && ($letter = static::EPSILON);
				$matrix[$state][$letter] = implode(static::STATE_SEPARATOR, array_keys($targets));
				!strlen($matrix[$state][$letter]) && ($matrix[$state][$letter] = static::NO_TARGET);

				$cell = max(strlen($letter), strlen($matrix[$state][$letter]), $cell);
			}

			!isset($alphabet) && ($alphabet = array_keys($matrix[$state]));
		}

		echo str_repeat(' ', $cell + 4);

		foreach ($alphabet as $letter) {
			$w = ($cell - strlen($letter)) / 2;
			echo str_repeat(' ', floor($w)) . $letter . str_repeat(' ', ceil($w));
		}

		echo "\n";

		foreach ($matrix as $state => $transitions) {
			echo ($a->isInitialState($state) ? static::INITIAL_S : ' ')
				. ($a->isFinalState($state) ? static::FINAL_S : ' ')
				. $state . str_repeat(' ', $cell + 2 - strlen($state));

			foreach ($transitions as $letter => $targets) {
				$w = ($cell - strlen($targets)) / 2;
				echo str_repeat(' ', floor($w)) . $targets . str_repeat(' ', ceil($w));
			}

			echo "\n";
		}
	}
}
