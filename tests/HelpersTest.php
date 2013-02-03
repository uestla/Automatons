<?php

use Automaton\Helpers;


class HelpersTest extends PHPUnit_Framework_TestCase
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

}
