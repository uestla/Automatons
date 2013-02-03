<?php

use Automaton\Helpers;
use Nette\Utils\Strings;

require_once __DIR__ . '/bootstrap.php';


class BasicTest extends PHPUnit_Framework_TestCase
{

	// === BASIC TESTS ====================================================

	function testHelpers()
	{
		$this->assertEquals(array(
			'one' => TRUE,
			'two' => TRUE,
			'three' => TRUE,

		), Helpers::valuesToKeys(array('one', 'two', 'three')));

		$this->assertTrue(Helpers::isSubsetOf(array('1', '2'), array('2', '1', '3')));
		$this->assertTrue(Helpers::isSubsetOf(array('1', '2', '3'), array('3', '2', '1', '3')));
		$this->assertFalse(Helpers::isSubsetOf(array('1', '2', '4'), array('3', '2', '1', '3')));

		$this->assertEquals('[]', Helpers::statesToString(array()));
		$this->assertEquals(array(), Helpers::stringToStates('[]'));
		$this->assertEquals('[1, 2, 3]', Helpers::statesToString(array('1', '2', '3')));
		$this->assertEquals(array('1', '2', '3'), Helpers::stringToStates('[1, 2, 3]'));

		$this->setExpectedException('Automaton\\InvalidInputException');
		Helpers::stringToStates('ahoj');
	}



	function testCreation()
	{
		try {
			new Automaton\Automaton(array(), '', array());
			$this->fail();

		} catch (Automaton\InvalidStateSetException $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array(),

			), '0', array());

			$this->fail();

		} catch (Automaton\InvalidAlphabetException $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array('' => '0'),

			), '0', array());

			$this->fail();

		} catch (Automaton\InvalidAlphabetException $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '1',
				),

			), '0', array());

			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '1',
				),
				'1' => array(
					'a' => '0',
					'b' => '1',
				),

			), '1', array());

			$this->fail();

		} catch (Automaton\InvalidTargetCount $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '1',
				),
				'1' => array(
					'b' => '1',
				),

			), '1', array());

			$this->fail();

		} catch (Automaton\SymbolNotFoundException $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '0',
				),

			), array('0', '1'), array());

			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}


		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '0',
				),

			), array('0'), array('1'));

			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}
	}



	function testGetters()
	{
		$a = $this->createFirstAutomaton();

		$this->assertEquals(array('a', 'b'), $a->getAlphabet());
		$this->assertEquals(array('0', '1', '2', '3', '4', '5', '6'), $a->getStates());
		$this->assertEquals(array('0'), $a->getInitials());
		$this->assertEquals(array('3', '6'), $a->getFinals());
	}



	function testDeterminism()
	{
		$a = $this->createFirstAutomaton();
		$this->assertFalse($a->isDeterministic());

		$a->removeEpsilon();
		$this->assertFalse($a->isDeterministic());
		$this->assertEquals($a, $a->removeEpsilon());

		$a->determinize();
		$this->assertTrue($a->isDeterministic());
		$this->assertEquals($a, $a->determinize());

		$this->assertFalse($this->createSecondAutomaton()->isDeterministic());
		$this->assertTrue($this->createThirdAutomaton()->isDeterministic());
		$this->assertFalse($this->createFourthAutomaton()->isDeterministic());
	}



	// === MANIPULATIONS ====================================================

	function testEpsilonRemoving()
	{
		$a = $this->createFirstAutomaton()->removeEpsilon();

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



	function testEpsilonClosure()
	{
		$a = $this->createFirstAutomaton();

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



	function testDeterminization()
	{
		$a = $this->createFirstAutomaton()->determinize();
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


		$b = $this->createSecondAutomaton()->determinize();
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

		$this->assertEquals($expected, $b);


		$c = $this->createFourthAutomaton()->determinize();

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

		$this->assertEquals($expected, $c);
	}



	function testMinimization()
	{
		$a = $this->createFirstAutomaton()->minimize();

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


		$b = $this->createThirdAutomaton()->minimize();

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

		$this->assertEquals($expected, $b);


		$c = $this->createFourthAutomaton()->minimize();

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

		$this->assertEquals($expected, $c);
	}



	function testNormalization()
	{
		$a = $this->createFifthAutomaton()->normalize();

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



	// === OPERATIONS ====================================================

	function testComplement()
	{
		$a = $this->createFirstAutomaton()->minimize()->getComplement();

		$this->assertEquals(new Automaton\Automaton(array(
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

		), array('1'), array('1', '2', '3')), $a);
	}



	// === RENDERING ====================================================

	function testRendering()
	{
		$a = $this->createFirstAutomaton();
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

		$b = $this->createSecondAutomaton()->determinize();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/second/determinized'));
		$actual = Strings::normalize((string) $b);
		$this->assertEquals($expected, $actual);
	}



	// === TESTING AUTOMATONS ====================================================

	protected function createFirstAutomaton()
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



	protected function createSecondAutomaton()
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



	protected function createThirdAutomaton()
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



	protected function createFourthAutomaton()
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



	protected function createFifthAutomaton()
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
