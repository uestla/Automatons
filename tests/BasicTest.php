<?php

use Automaton\Helpers;
use Nette\Utils\Strings;

require_once __DIR__ . '/bootstrap.php';


class BasicTest extends PHPUnit_Framework_TestCase
{

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



	function testDeterminism()
	{
		$a = $this->createTestingAutomaton();
		$this->assertFalse($a->isDeterministic());

		$a->removeEpsilon();
		$this->assertFalse($a->isDeterministic());
		$this->assertEquals($a, $a->removeEpsilon());

		$a->determinize();
		$this->assertTrue($a->isDeterministic());
		$this->assertEquals($a, $a->determinize());


		$b = new Automaton\Automaton(array(
			'1' => array(
				'a' => array(),
				'b' => array('2', '3'),
			),
			'2' => array(
				'a' => array('2', '3'),
				'b' => array('3'),
			),
			'3' => array(
				'a' => array('1'),
				'b' => array(),
			),

		), array('1', '2'), array('1'));

		$b->determinize();

		$this->assertEquals(new Automaton\Automaton(array(
			'[1, 2]' => array(
				'a' => array('[2, 3]'),
				'b' => array('[2, 3]'),
			),
			'[2, 3]' => array(
				'a' => array('[1, 2, 3]'),
				'b' => array('[3]'),
			),
			'[1, 2, 3]' => array(
				'a' => array('[1, 2, 3]'),
				'b' => array('[2, 3]'),
			),
			'[3]' => array(
				'a' => array('[1]'),
				'b' => array('[]'),
			),
			'[1]' => array(
				'a' => array('[]'),
				'b' => array('[2, 3]'),
			),
			'[]' => array(
				'a' => array('[]'),
				'b' => array('[]'),
			),

		), array('[1, 2]'), array('[1, 2]', '[1, 2, 3]', '[1]')), $b);
	}



	function testEpsilonRemoving()
	{
		$a = $this->createTestingAutomaton()->removeEpsilon();

		$this->assertEquals(array(
			'0' => array(
				'a' => array('0', '1'),
				'b' => array('0', '4', '5'),
			),
			'1' => array(
				'a' => array('4', '5', '6'),
				'b' => array('2'),
			),
			'2' => array(
				'a' => array('3', '6'),
				'b' => array('5', '6'),
			),
			'3' => array(
				'a' => array('3'),
				'b' => array('3'),
			),
			'4' => array(
				'a' => array(),
				'b' => array('5'),
			),
			'5' => array(
				'a' => array('6'),
				'b' => array(),
			),
			'6' => array(
				'a' => array('6'),
				'b' => array('6'),
			),

		), $a->getTransitions());


		$this->assertTrue(count(array_diff(array('2', '3', '6'), $a->getFinals())) === 0);
	}



	function testEpsilonClosure()
	{
		$a = $this->createTestingAutomaton();

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
		$a = $this->createTestingAutomaton()->determinize();

		$this->assertEquals(array(
			'[0]' => array(
				'a' => array('[0, 1]'),
				'b' => array('[0, 4, 5]'),
			),
			'[0, 1]' => array(
				'a' => array('[0, 1, 4, 5, 6]'),
				'b' => array('[0, 2, 4, 5]'),
			),
			'[0, 4, 5]' => array(
				'a' => array('[0, 1, 6]'),
				'b' => array('[0, 4, 5]'),
			),
			'[0, 1, 4, 5, 6]' => array(
				'a' => array('[0, 1, 4, 5, 6]'),
				'b' => array('[0, 2, 4, 5, 6]'),
			),
			'[0, 2, 4, 5]' => array(
				'a' => array('[0, 1, 3, 6]'),
				'b' => array('[0, 4, 5, 6]'),
			),
			'[0, 1, 6]' => array(
				'a' => array('[0, 1, 4, 5, 6]'),
				'b' => array('[0, 2, 4, 5, 6]'),
			),
			'[0, 2, 4, 5, 6]' => array(
				'a' => array('[0, 1, 3, 6]'),
				'b' => array('[0, 4, 5, 6]'),
			),
			'[0, 1, 3, 6]' => array(
				'a' => array('[0, 1, 3, 4, 5, 6]'),
				'b' => array('[0, 2, 3, 4, 5, 6]'),
			),
			'[0, 4, 5, 6]' => array(
				'a' => array('[0, 1, 6]'),
				'b' => array('[0, 4, 5, 6]'),
			),
			'[0, 1, 3, 4, 5, 6]' => array(
				'a' => array('[0, 1, 3, 4, 5, 6]'),
				'b' => array('[0, 2, 3, 4, 5, 6]'),
			),
			'[0, 2, 3, 4, 5, 6]' => array(
				'a' => array('[0, 1, 3, 6]'),
				'b' => array('[0, 3, 4, 5, 6]'),
			),
			'[0, 3, 4, 5, 6]' => array(
				'a' => array('[0, 1, 3, 6]'),
				'b' => array('[0, 3, 4, 5, 6]'),
			),

		), $a->getTransitions());
	}



	function testMinimization()
	{
		$a = $this->createTestingAutomaton()->minimize();

		$this->assertEquals(new Automaton\Automaton(array(
			'I' => array(
				'a' => array('II'),
				'b' => array('III'),
			),
			'II' => array(
				'a' => array('IIII'),
				'b' => array('IIII'),
			),
			'III' => array(
				'a' => array('IIII'),
				'b' => array('III'),
			),
			'IIII' => array(
				'a' => array('IIII'),
				'b' => array('IIII'),
			),

		), array('I'), array('IIII')), $a);
	}



	function testComplement()
	{
		$a = $this->createTestingAutomaton()->minimize()->getComplement();
		$this->assertEquals(new Automaton\Automaton(array(
			'I' => array(
				'a' => array('II'),
				'b' => array('III'),
			),
			'II' => array(
				'a' => array('IIII'),
				'b' => array('IIII'),
			),
			'III' => array(
				'a' => array('IIII'),
				'b' => array('III'),
			),
			'IIII' => array(
				'a' => array('IIII'),
				'b' => array('IIII'),
			),

		), array('I'), array('I', 'II', 'III')), $a);
	}



	function testRendering()
	{
		$a = $this->createTestingAutomaton();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/basic'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);

		$a->removeEpsilon();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/epsilon-removed'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);

		$a->determinize();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/determinized'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);

		$a->minimize();
		$expected = Strings::normalize(file_get_contents('safe://' . __DIR__ . '/rendering/minimized'));
		$actual = Strings::normalize((string) $a);
		$this->assertEquals($expected, $actual);
	}



	// === HELPERS ====================================================

	protected function createTestingAutomaton()
	{
		return new Automaton\Automaton(
			array(
				'0' => array(
					'a' => array('0', '1'),
					'b' => array('0', '4'),
					'' => array('4'),
				),
				'1' => array(
					'a' => array('4', '5'),
					'b' => array('2'),
					'' => array('5'),
				),
				'2' => array(
					'a' => array('3'),
					'b' => array('5', '6'),
					'' => array('6'),
				),
				'3' => array(
					'a' => array('3'),
					'b' => array('3'),
					'' => array(),
				),
				'4' => array(
					'a' => array(),
					'b' => array('5'),
					'' => array(),
				),
				'5' => array(
					'a' => array('6'),
					'b' => array(),
					'' => array(),
				),
				'6' => array(
					'a' => array('6'),
					'b' => array('6'),
					'' => array(),
				),
			),
			'0',
			array('3', '6')
		);
	}

}
