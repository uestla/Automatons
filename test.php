<?php

require_once __DIR__ . '/src/Automaton.php';

$states = array(
	'A' => array(
		'a' => array('B'),
		'b' => array(),
		'c' => array('C', 'A'),
		'' => array(),
	),
	'B' => array(
		'a' => array('A'),
		'b' => array('A'),
		'c' => array('A', 'B'),
		'' => array('A'),
	),
	'C' => array(
		'a' => array('C'),
		'b' => array('C'),
		'c' => array('C'),
		'' => array(),
	),
);

$initials = array('A');
$finals = array('A', 'C');

$automaton = new Automaton\Automaton($states, $initials, $finals);
echo $automaton->removeEpsilon()->determinize()->minimize()->normalize(); die();



require_once __DIR__ . '/src/factories/FileFactory.php';

$factory = new Automaton\FileFactory(__DIR__ . '/automaton.txt');
$automaton = $factory->create()->determinize()->minimize()->normalize();
echo $automaton;
