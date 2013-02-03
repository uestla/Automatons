<?php

use Nette\Utils\Strings;


class SecondAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === MANIPULATIONS ====================================================

	function testDeterminization()
	{
		$a = static::createAutomaton()->determinize();

		$expected = new Automaton\Automaton(array(
			'[1, 2]' => array(
				'a' => '[2, 3]',
				'b' => '[2, 3]',
			),
			'[2, 3]' => array(
				'a' => '[1, 2, 3]',
				'b' => '[3]',
			),
			'[1, 2, 3]' => array(
				'a' => '[1, 2, 3]',
				'b' => '[2, 3]',
			),
			'[3]' => array(
				'a' => '[1]',
				'b' => '[]',
			),
			'[1]' => array(
				'a' => '[]',
				'b' => '[2, 3]',
			),
			'[]' => array(
				'a' => '[]',
				'b' => '[]',
			),

		), array('[1, 2]'), array('[1, 2]', '[1, 2, 3]', '[1]'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
	}



	function testDeterminism()
	{
		$this->assertFalse(static::createAutomaton()->isDeterministic());
	}



	// === RENDERING ====================================================

	function testRendering()
	{
		$a = static::createAutomaton()->determinize();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/second/determinized'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);
	}



	// === AUTOMATON ====================================================

	protected static function createAutomaton()
	{
		return new Automaton\Automaton(array(
			'1' => array(
				'a' => array(),
				'b' => array('2', '3'),
			),
			'2' => array(
				'a' => array('2', '3'),
				'b' => '3',
			),
			'3' => array(
				'a' => '1',
				'b' => array(),
			),

		), array('1', '2'), array('1'));
	}

}
