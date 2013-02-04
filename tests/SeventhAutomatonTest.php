<?php


class SeventhAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === INPUT TESTING ====================================================

	function testInput()
	{
		$this->assertTrue(static::createAutomaton()->testInput('babba'));
		$this->assertTrue(static::createAutomaton()->normalize()->testInput('babba'));
	}



	// === AUTOMATON ====================================================

	protected static function createAutomaton()
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

}
