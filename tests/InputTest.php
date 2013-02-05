<?php


class InputTest extends PHPUnit_Framework_TestCase
{

	// === INPUT TESTING ====================================================

	function testInput()
	{
		$this->assertTrue(static::createFirstAutomaton()->testInput('babba'));
		$this->assertTrue(static::createFirstAutomaton()->normalize()->testInput('babba'));

		$this->assertFalse(static::createFirstAutomaton()->testInput(''));


		$this->assertTrue(static::createSecondAutomaton()->testInput('ababb'));
		$this->assertTrue(static::createSecondAutomaton()->normalize()->testInput('ababb'));
		$this->assertTrue(static::createSecondAutomaton()->testInput(''));
		$this->assertTrue(static::createSecondAutomaton()->testInput('bbb'));

		$this->assertFalse(static::createSecondAutomaton()->testInput('a'));
		$this->assertFalse(static::createSecondAutomaton()->testInput('ěřýěéščřá'));


		$this->assertFalse(static::createThirdAutomaton()->testInput(''));
		$this->assertFalse(static::createThirdAutomaton()->testInput('a'));
		$this->assertFalse(static::createThirdAutomaton()->testInput('bbb'));


		$this->assertTrue(static::createEpsilonAutomaton()->testInput(''));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('a'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('b'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('c'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('ab'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('bc'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('ac'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('aaa'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('abc'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('abc'));
		$this->assertTrue(static::createEpsilonAutomaton()->testInput('aaabbc'));
		$this->assertTrue(static::createEpsilonAutomaton()->normalize()->testInput('aaabbc'));


		$this->assertTrue(static::createMultiSymbolAutomaton()->testInput(''));
		$this->assertTrue(static::createMultiSymbolAutomaton()->testInput('a'));
		$this->assertTrue(static::createMultiSymbolAutomaton()->testInput('aa'));
		$this->assertTrue(static::createMultiSymbolAutomaton()->testInput('aaa'));
		$this->assertTrue(static::createMultiSymbolAutomaton()->testInput('aaaa'));
	}



	// === AUTOMATON ====================================================

	protected static function createFirstAutomaton()
	{
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => array(),
				'b' => array('1', '2'),
			),
			'2' => array(
				'a' => array('3', '1'),
				'b' => array(),
			),
			'3' => array(
				'a' => array(),
				'b' => array(),
			),

		), array('1', '2'), '3');
	}



	protected static function createSecondAutomaton()
	{
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => array('2', '4', '3'),
				'b' => array('1'),
			),
			'2' => array(
				'a' => array(),
				'b' => array('5'),
			),
			'3' => array(
				'a' => array(),
				'b' => array('4'),
			),
			'4' => array(
				'a' => array('2'),
				'b' => array('3', '5'),
			),
			'5' => array(
				'a' => array(),
				'b' => array('5'),
			),

		), array('1', '3'), array('1', '5'));
	}



	protected static function createThirdAutomaton()
	{
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => array('2', '4', '3'),
				'b' => array('1'),
			),
			'2' => array(
				'a' => array(),
				'b' => array('5'),
			),
			'3' => array(
				'a' => array(),
				'b' => array('4'),
			),
			'4' => array(
				'a' => array('2'),
				'b' => array('3', '5'),
			),
			'5' => array(
				'a' => array(),
				'b' => array('5'),
			),

		), array('1', '3'), array());
	}



	protected static function createEpsilonAutomaton()
	{
		return new Automaton\Automaton(array(
			'q0' => array(
				'a' => 'q0',
				'b' => array(),
				'c' => array(),
				'' => 'q1',
			),
			'q1' => array(
				'a' => array(),
				'b' => 'q1',
				'c' => array(),
				'' => 'q2',
			),
			'q2' => array(
				'a' => array(),
				'b' => array(),
				'c' => 'q2',
				'' => array(),
			),

		), 'q0', 'q2');
	}



	protected static function createMultiSymbolAutomaton()
	{
		// accepts a*aa
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => array('1'),
				'aaa' => array('2'),
			),
			'2' => array(
				'a' => array(),
				'aaa' => array(),
			),

		), '1', array('1', '2'));
	}

}
