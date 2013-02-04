<?php

use Nette\Utils\Strings;


class FirstAutomatonTest extends PHPUnit_Framework_TestCase
{

	// === GETTERS ====================================================

	function testGetters()
	{
		$a = static::createAutomaton();

		$this->assertEquals(array('a', 'b'), $a->getAlphabet());
		$this->assertEquals(array('0', '1', '2', '3', '4', '5', '6'), $a->getStates());
		$this->assertEquals(array('0'), $a->getInitials());
		$this->assertEquals(array('3', '6'), $a->getFinals());
	}



	function testStateExistence()
	{
		$a = static::createAutomaton();

		try {
			$a->isInitialState('asdf');
			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}


		try {
			$a->isFinalState('asdf');
			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}
	}



	// === MANIPULATIONS ====================================================

	function testEpsilonClosure()
	{
		$a = static::createAutomaton();

		$this->assertEquals(array('0', '4'), $a->epsilonClosure('0'));
		$this->assertEquals(array('1', '5'), $a->epsilonClosure('1'));
		$this->assertEquals(array('2', '6'), $a->epsilonClosure('2'));
		$this->assertEquals(array('3'), $a->epsilonClosure('3'));
		$this->assertEquals(array('4'), $a->epsilonClosure('4'));
		$this->assertEquals(array('5'), $a->epsilonClosure('5'));
		$this->assertEquals(array('6'), $a->epsilonClosure('6'));

		$a->removeEpsilon();
		$this->assertEquals(array('0'), $a->epsilonClosure('0'));
	}



	function testEpsilonRemoving()
	{
		$a = static::createAutomaton()->removeEpsilon();

		$expected = new Automaton\Automaton(array(
			'0' => array(
				'a' => array('0', '1'),
				'b' => array('0', '4', '5'),
			),
			'1' => array(
				'a' => array('4', '5', '6'),
				'b' => '2',
			),
			'2' => array(
				'a' => array('3', '6'),
				'b' => array('5', '6'),
			),
			'3' => array(
				'a' => '3',
				'b' => '3',
			),
			'4' => array(
				'a' => array(),
				'b' => '5',
			),
			'5' => array(
				'a' => '6',
				'b' => array(),
			),
			'6' => array(
				'a' => '6',
				'b' => '6',
			),

		), array('0'), array('2', '3', '6'));

		$this->assertEquals($expected, $a);
	}



	function testDeterminization()
	{
		$a = static::createAutomaton()->determinize();

		$expected = new Automaton\Automaton(array(
			'[0]' => array(
				'a' => '[0, 1]',
				'b' => '[0, 4, 5]',
			),
			'[0, 1]' => array(
				'a' => '[0, 1, 4, 5, 6]',
				'b' => '[0, 2, 4, 5]',
			),
			'[0, 4, 5]' => array(
				'a' => '[0, 1, 6]',
				'b' => '[0, 4, 5]',
			),
			'[0, 1, 4, 5, 6]' => array(
				'a' => '[0, 1, 4, 5, 6]',
				'b' => '[0, 2, 4, 5, 6]',
			),
			'[0, 2, 4, 5]' => array(
				'a' => '[0, 1, 3, 6]',
				'b' => '[0, 4, 5, 6]',
			),
			'[0, 1, 6]' => array(
				'a' => '[0, 1, 4, 5, 6]',
				'b' => '[0, 2, 4, 5, 6]',
			),
			'[0, 2, 4, 5, 6]' => array(
				'a' => '[0, 1, 3, 6]',
				'b' => '[0, 4, 5, 6]',
			),
			'[0, 1, 3, 6]' => array(
				'a' => '[0, 1, 3, 4, 5, 6]',
				'b' => '[0, 2, 3, 4, 5, 6]',
			),
			'[0, 4, 5, 6]' => array(
				'a' => '[0, 1, 6]',
				'b' => '[0, 4, 5, 6]',
			),
			'[0, 1, 3, 4, 5, 6]' => array(
				'a' => '[0, 1, 3, 4, 5, 6]',
				'b' => '[0, 2, 3, 4, 5, 6]',
			),
			'[0, 2, 3, 4, 5, 6]' => array(
				'a' => '[0, 1, 3, 6]',
				'b' => '[0, 3, 4, 5, 6]',
			),
			'[0, 3, 4, 5, 6]' => array(
				'a' => '[0, 1, 3, 6]',
				'b' => '[0, 3, 4, 5, 6]',
			),

		), array('[0]'), array('[0, 1, 4, 5, 6]', '[0, 2, 4, 5]', '[0, 1, 6]', '[0, 2, 4, 5, 6]', '[0, 1, 3, 6]', '[0, 4, 5, 6]', '[0, 1, 3, 4, 5, 6]', '[0, 2, 3, 4, 5, 6]', '[0, 3, 4, 5, 6]'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
	}



	function testMinimization()
	{
		$a = static::createAutomaton()->minimize();

		$expected = new Automaton\Automaton(array(
			'1' => array(
				'a' => '2',
				'b' => '3',
			),
			'2' => array(
				'a' => '4',
				'b' => '4',
			),
			'3' => array(
				'a' => '4',
				'b' => '3',
			),
			'4' => array(
				'a' => '4',
				'b' => '4',
			),

		), array('1'), array('4'));

		$expected->isDeterministic(); // intentionally due to lazy determinism property initialization

		$this->assertEquals($expected, $a);
		$this->assertEquals($expected, $a->minimize());
	}



	function testDeterminism()
	{
		$a = static::createAutomaton();
		$this->assertFalse($a->isDeterministic());

		$a->removeEpsilon();
		$this->assertFalse($a->isDeterministic());
		$this->assertEquals($a, $a->removeEpsilon());

		$a->determinize();
		$this->assertTrue($a->isDeterministic());

		$a->minimize();
		$this->assertTrue($a->isDeterministic());

		$a->normalize();
		$this->assertTrue($a->isDeterministic());
	}



	// === OPERATIONS ====================================================

	function testComplement()
	{
		$a = static::createAutomaton()->getComplement();

		$this->assertEquals(new Automaton\Automaton(
				static::createAutomaton()->getTransitions(),
				'0', array('0', '1', '2', '4', '5')), $a);
	}



	// === RENDERING ====================================================

	function testRendering()
	{
		$a = static::createAutomaton();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/first/basic'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);

		$a->removeEpsilon();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/first/epsilon-removed'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);

		$a->determinize();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/first/determinized'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);

		$a->minimize();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/first/minimized'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);
	}



	// === AUTOMATON ====================================================

	protected static function createAutomaton()
	{
		return new Automaton\Automaton(array(
			'0' => array(
				'a' => array('0', '1'),
				'b' => array('0', '4'),
				'' => '4',
			),
			'1' => array(
				'a' => array('4', '5'),
				'b' => '2',
				'' => '5',
			),
			'2' => array(
				'a' => '3',
				'b' => array('5', '6'),
				'' => '6',
			),
			'3' => array(
				'a' => '3',
				'b' => '3',
				'' => array(),
			),
			'4' => array(
				'a' => array(),
				'b' => '5',
				'' => array(),
			),
			'5' => array(
				'a' => '6',
				'b' => array(),
				'' => array(),
			),
			'6' => array(
				'a' => '6',
				'b' => '6',
				'' => array(),
			),

		), '0', array('3', '6'));
	}

}
