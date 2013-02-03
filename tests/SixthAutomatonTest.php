<?php


class SixthAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === MANIPULATIONS ====================================================

	function testMinimization()
	{
		$a = static::createAutomaton()->minimize();

		$expected = new Automaton\Automaton(array(
			'1' => array(
				'0' => '1',
				'1' => '3',
			),
			'2' => array(
				'0' => '1',
				'1' => '2',
			),
			'3' => array(
				'0' => '4',
				'1' => '2',
			),
			'4' => array(
				'0' => '4',
				'1' => '1',
			),

		), array('1'), array('3', '4'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
	}



	// === AUTOMATON ====================================================

	protected static function createAutomaton()
	{
		return new Automaton\Automaton(array(
			'q0' => array(
				'0' => 'q0',
				'1' => 'q2',
			),
			'q1' => array(
				'0' => 'q0',
				'1' => 'q2',
			),
			'q2' => array(
				'0' => 'q5',
				'1' => 'q4',
			),
			'q3' => array(
				'0' => 'q0',
				'1' => 'q4',
			),
			'q4' => array(
				'0' => 'q1',
				'1' => 'q3',
			),
			'q5' => array(
				'0' => 'q5',
				'1' => 'q1',
			),

		), array('q0'), array('q2', 'q5'));
	}

}
