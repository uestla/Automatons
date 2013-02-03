<?php


class FifthAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === MANIPULATIONS ====================================================

	function testNormalization()
	{
		$a = static::createAutomaton()->normalize();

		$expected = new Automaton\Automaton(array(
			'1' => array(
				'0' => '2',
				'1' => '3',
			),
			'2' => array(
				'0' => '4',
				'1' => '5',
			),
			'3' => array(
				'0' => '4',
				'1' => '2',
			),
			'4' => array(
				'0' => '6',
				'1' => '5',
			),
			'5' => array(
				'0' => '2',
				'1' => '6',
			),
			'6' => array(
				'0' => '3',
				'1' => '1',
			),

		), array('1'), array('5', '6'));

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
			'q0' => array(
				'0' => 'q2',
				'1' => 'q1',
			),
			'q1' => array(
				'0' => 'q4',
				'1' => 'q2',
			),
			'q2' => array(
				'0' => 'q4',
				'1' => 'q5',
			),
			'q3' => array(
				'0' => 'q1',
				'1' => 'q0',
			),
			'q4' => array(
				'0' => 'q3',
				'1' => 'q5',
			),
			'q5' => array(
				'0' => 'q2',
				'1' => 'q3',
			),

		), array('q0'), array('q3', 'q5'));
	}

}
