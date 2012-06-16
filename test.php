<?php

require_once __DIR__ . '/vendors/Nette/nette.min.php';
require_once __DIR__ . '/src/Automaton.php';

Nette\Diagnostics\Debugger::enable( Nette\Diagnostics\Debugger::DEVELOPMENT );
Nette\Diagnostics\Debugger::$maxDepth = FALSE;

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

$automaton = new Automaton($states, $initials, $finals);
dump($automaton); die();
