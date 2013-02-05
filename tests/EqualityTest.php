<?php


class EqualityTest extends PHPUnit_Framework_TestCase
{

	// === OPERATIONS ====================================================

	function testFormalEquality()
	{
		$this->assertTrue(static::createFirstAutomaton()->equals(static::createFirstAutomaton()));
		$this->assertTrue(static::createFirstAutomaton()->equals(static::createSecondAutomaton()));
	}



	function testNormalization()
	{
		$this->assertEquals(
			static::createFirstAutomaton()->normalize(),
			static::createSecondAutomaton()->normalize()
		);
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
			'I' => array(
				'a' => 'II',
				'b' => 'V',
			),
			'II' => array(
				'a' => 'V',
				'b' => 'III',
			),
			'III' => array(
				'a' => 'IV',
				'b' => 'VII',
			),
			'IV' => array(
				'a' => 'IV',
				'b' => 'IV',
			),
			'V' => array(
				'a' => 'VI',
				'b' => 'III',
			),
			'VI' => array(
				'a' => 'VII',
				'b' => 'III',
			),
			'VII' => array(
				'a' => 'VII',
				'b' => 'VIII',
			),
			'VIII' => array(
				'a' => 'VIII',
				'b' => 'VIII',
			),

		), array('I'), array('V', 'VIII'));
	}

}
