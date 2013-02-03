<?php


class FourthAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === MANIPULATIONS ====================================================

	function testDeterminization()
	{
		$a = static::createAutomaton()->determinize();

		$expected = new Automaton\Automaton(array(
			'[q0]' => array(
				'0' => '[q1]',
				'1' => '[q0]',
			),
			'[q1]' => array(
				'0' => '[q2]',
				'1' => '[q1]',
			),
			'[q2]' => array(
				'0' => '[q3]',
				'1' => '[q2]',
			),
			'[q3]' => array(
				'0' => '[q4]',
				'1' => '[q3]',
			),
			'[q4]' => array(
				'0' => '[q2]',
				'1' => '[q4]',
			),

		), array('[q0]'), array('[q2]'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
	}



	function testMinimization()
	{
		$a = static::createAutomaton()->minimize();

		$expected = new Automaton\Automaton(array(
			'1' => array(
				'0' => '2',
				'1' => '1',
			),
			'2' => array(
				'0' => '3',
				'1' => '2',
			),
			'3' => array(
				'0' => '1',
				'1' => '3',
			),
		), array('1'), array('3'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
	}



	function testDeterminism()
	{
		$this->assertFalse(static::createAutomaton()->isDeterministic());
	}



	// === AUTOMATON ====================================================

	protected static function createAutomaton()
	{
		return new Automaton\Automaton(array(
			'q0' => array(
				'0' => 'q1',
				'1' => 'q0',
			),
			'q1' => array(
				'0' => 'q2',
				'1' => 'q1',
			),
			'q2' => array(
				'0' => 'q3',
				'1' => 'q2',
			),
			'q3' => array(
				'0' => 'q4',
				'1' => 'q3',
			),
			'q4' => array(
				'0' => 'q2',
				'1' => 'q4',
			),
			'q5' => array(
				'0' => 'q1',
				'1' => 'q4',
			),
			'q6' => array(
				'0' => 'q3',
				'1' => 'q4',
			),
			'q7' => array(
				'0' => 'q6',
				'1' => 'q5',
			),
			'q8' => array(
				'0' => 'q7',
				'1' => 'q5',
			),

		), array('q0'), array('q2'));
	}

}
