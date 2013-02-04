<?php


class SeventhAutomatonTest extends PHPUnit_Framework_TestCase
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

}
