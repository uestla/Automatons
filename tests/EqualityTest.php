<?php


class EqualityTest extends PHPUnit_Framework_TestCase
{

	// === OPERATIONS ====================================================

	function testEquality()
	{
		$this->assertTrue(static::createFirstAutomaton()->isEqual(static::createFirstAutomaton()));
		$this->assertTrue(static::createFirstAutomaton()->isEqual(static::createSecondAutomaton()));
	}



	// === AUTOMATONS ====================================================

	protected static function createFirstAutomaton()
	{
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => '5',
				'b' => '2',
			),
			'2' => array(
				'a' => '3',
				'b' => '7',
			),
			'3' => array(
				'a' => '4',
				'b' => '7',
			),
			'4' => array(
				'a' => '4',
				'b' => '8',
			),
			'5' => array(
				'a' => '2',
				'b' => '7',
			),
			'6' => array(
				'a' => '6',
				'b' => '6',
			),
			'7' => array(
				'a' => '6',
				'b' => '4',
			),
			'8' => array(
				'a' => '8',
				'b' => '8',
			),

		), array('1'), array('2', '8'));
	}



	static function createSecondAutomaton()
	{
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => '2',
				'b' => '5',
			),
			'2' => array(
				'a' => '5',
				'b' => '3',
			),
			'3' => array(
				'a' => '4',
				'b' => '7',
			),
			'4' => array(
				'a' => '4',
				'b' => '4',
			),
			'5' => array(
				'a' => '6',
				'b' => '3',
			),
			'6' => array(
				'a' => '7',
				'b' => '3',
			),
			'7' => array(
				'a' => '7',
				'b' => '8',
			),
			'8' => array(
				'a' => '8',
				'b' => '8',
			),

		), array('1'), array('5', '8'));
	}

}
