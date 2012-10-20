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
		$cell = 0;
		$states = $a->getStates();
		$alphabet = $a->getAlphabet();

		foreach ($states as $state => $info) {
			foreach ($info['transitions'] as $letter => $targets) {
				$states[$state]['transitions'][$letter] = implode(static::STATE_SEPARATOR, $targets);
				!strlen($states[$state]['transitions'][$letter]) && ($states[$state]['transitions'][$letter] = static::NO_TARGET);
				$cell = max(strlen($letter), strlen($states[$state]['transitions'][$letter]), $cell);
			}
		}

		$cell += 2;

		echo str_repeat(' ', $cell + 2);

		foreach ($alphabet as $letter) {
			$letter === '' && ($letter = static::EPSILON);
			$w = ($cell - strlen($letter)) / 2;
			echo str_repeat(' ', floor($w)) . $letter . str_repeat(' ', ceil($w));
		}

		echo "\n";

		foreach ($states as $state => $info) {
			echo str_pad( ($info['initial'] ? static::INITIAL_S : '') . ($info['final'] ? static::FINAL_S : ''), strlen($state) + 2, ' ', STR_PAD_LEFT )
					. $state . str_repeat(' ', $cell - strlen($state));

			foreach ($info['transitions'] as $letter => $targets) {
				$w = ($cell - strlen($targets)) / 2;
				echo str_repeat(' ', floor($w)) . $targets . str_repeat(' ', ceil($w));
			}

			echo "\n";
		}
	}
}
