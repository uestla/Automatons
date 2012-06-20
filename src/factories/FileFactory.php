<?php

namespace Automaton;


class FileFactory implements IFactory
{
	/** @var string */
	private $file;



	const EPSILON = '\\eps';
	const EMPTY_TARGET = '-';
	const NFA = 'NFA';
	const DFA = 'DFA';
	const INITIAL_S = '>';
	const FINAL_S = '<';
	const STATE_SEPARATOR = '|';



	function __construct($file)
	{
		$this->file = $file;
	}



	/** @return Automaton */
	function create()
	{
		$path = realpath($this->file);
		if ($path === FALSE) {
			throw new FileNotFoundException("File '{$this->file}' not found.");
		}

		$states = $initials = $finals = $alphabet = array();

		foreach (file($path) as $line) {
			$line = trim($line);
			if (!strlen($line)) {
				continue;
			}

			if (!count($alphabet)) {
				$line = preg_replace('#^\s*(?:' . preg_quote(static::NFA, '#') . '|' . preg_quote(static::DFA,'#') . ')?\s*#', '', $line);
				$parts = preg_split('#\s+#', $line);

				if (!count($parts)) {
					throw new InvalidInputException("Empty alphabet detected.");
				}

				foreach ($parts as $letter) {
					if ($letter === static::EPSILON) {
						$letter = '';
					}

					if (in_array($letter, $alphabet, TRUE)) {
						throw new InvalidInputException("Duplicate letter '{$letter}' found in the alphabet.");
					}

					$alphabet[] = $letter;
				}

				continue;
			}

			$transitions = preg_split('#\s+#', $line);
			$state = array_shift($transitions);

			if (count($transitions) !== count($alphabet)) {
				throw new InvalidInputException("Transition and letter count don't match.");
			}

			$ss = array();
			$this->parseStateName($state, $ss, $init, $final);

			foreach ($ss as $state) {
				if (!strlen($state)) {
					throw new InvalidInputException("State name not specified.");
				}

				if ($init) {
					$initials[] = $state;
				}

				if ($final) {
					$finals[] = $state;
				}

				if (!isset($states[$state])) {
					$states[$state] = array();
				}

				foreach ($transitions as $offset => $targets) {
					$targets = $targets === static::EMPTY_TARGET ? array() : explode(static::STATE_SEPARATOR, $targets);

					if (isset($states[$state][$alphabet[$offset]])) {
						foreach ($targets as $target) {
							if (!in_array($target, $states[$state][$alphabet[$offset]], TRUE)) {
								$states[$state][$alphabet[$offset]][] = $target;
							}
						}

					} else {
						$states[$state][$alphabet[$offset]] = $targets;
					}
				}
			}
		}

		return new Automaton($states, $initials, $finals);
	}



	/**
	 * @param  string
	 * @param  array
	 * @param  bool
	 * @param  bool
	 * @return void
	 */
	private function parseStateName($input, array & $states, & $init, & $final)
	{
		$i = preg_quote(static::INITIAL_S, '#');
		$f = preg_quote(static::FINAL_S, '#');
		$matched = preg_match("#^(?:${i}${f}|${f}${i}|$i|$f)#", $input, $m);

		if ($matched) {
			$input = substr($input, strlen($m[0]));
		}

		$init = $matched && strpos($m[0], static::INITIAL_S) !== FALSE;
		$final = $matched && strpos($m[0], static::FINAL_S) !== FALSE;

		$states = explode(static::STATE_SEPARATOR, $input);
	}
}



class WrongFormatException extends \Exception {}
class InvalidInputException extends \Exception {}
class FileNotFoundException extends \Exception {}
