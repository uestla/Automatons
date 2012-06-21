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
		$states = $a->getStates();
		$alphabet = $a->getAlphabet();

		foreach ($states as $state => $info) {
			foreach ($info['transitions'] as $letter => $targets) {
				unset($states[$state]['transitions'][$letter]);

				$letter === '' && ($letter = static::EPSILON);
				$states[$state]['transitions'][$letter] = implode(static::STATE_SEPARATOR, $targets);
				!strlen($states[$state]['transitions'][$letter]) && ($states[$state]['transitions'][$letter] = static::NO_TARGET);
			}

			$cell = max(strlen($letter), strlen($states[$state]['transitions'][$letter]), $cell);
		}

		echo str_repeat(' ', $cell + 4);

		foreach ($alphabet as $letter) {
			$w = ($cell - strlen($letter)) / 2;
			echo str_repeat(' ', floor($w)) . $letter . str_repeat(' ', ceil($w));
		}

		echo "\n";

		foreach ($states as $state => $info) {
			echo ($info['initial'] ? static::INITIAL_S : ' ')
				. ($info['final'] ? static::FINAL_S : ' ')
				. $state . str_repeat(' ', $cell + 2 - strlen($state));

			foreach ($info['transitions'] as $letter => $targets) {
				$w = ($cell - strlen($targets)) / 2;
				echo str_repeat(' ', floor($w)) . $targets . str_repeat(' ', ceil($w));
			}

			echo "\n";
		}
	}
}
