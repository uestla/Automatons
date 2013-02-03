<?php


class ThirdAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === MANIPULATIONS ====================================================

	function testMinimization()
	{
		$a = static::createAutomaton()->minimize();

		$expected = new Automaton\Automaton(array(
			'1' => array(
				'0' => '3',
				'1' => '3',
			),
			'2' => array(
				'0' => '2',
				'1' => '2',
			),
			'3' => array(
				'0' => '2',
				'1' => '3',
			),

		), array('1'), array('3'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
	}



	function testDeterminism()
	{
		$this->assertTrue(static::createAutomaton()->isDeterministic());
	}



	// === AUTOMATON ====================================================

	protected static function createAutomaton()
	{
		return new Automaton\Automaton(array(
			'q1' => array(
				'0' => 'q3',
				'1' => 'q2',
			),
			'q2' => array(
				'0' => 'q4',
				'1' => 'q2',
			),
			'q3' => array(
				'0' => 'q4',
				'1' => 'q3',
			),
			'q4' => array(
				'0' => 'q4',
				'1' => 'q4',
			),

		), array('q1'), array('q2', 'q3'));
	}

}
