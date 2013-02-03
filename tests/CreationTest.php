<?php


class CreationTest extends PHPUnit_Framework_TestCase
{

	function testStateSet()
	{
		try {
			new Automaton\Automaton(array(), '', array());
			$this->fail();

		} catch (Automaton\InvalidStateSetException $e) {}
	}



	function testAlphabetEmptiness()
	{
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
	}



	function testTargetsExistence()
	{
		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '1',
				),

			), '0', array());

			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}
	}



	function testEqualTargetCount()
	{
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
	}



	function testTransitionSymbolExistence()
	{
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
	}



	function testInitialsNonEmptiness()
	{
		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '0',
				),

			), array(), array());

			$this->fail();

		} catch (Automaton\InvalidInitialsSetException $e) {}
	}



	function testInitialsExistence()
	{
		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '0',
				),

			), array('0', '1'), array());

			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}
	}



	function testFinalsExistence()
	{
		try {
			new Automaton\Automaton(array(
				'0' => array(
					'a' => '0',
				),

			), array('0'), array('1'));

			$this->fail();

		} catch (Automaton\StateNotFoundException $e) {}
	}

}
