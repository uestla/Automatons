<?php

require_once __DIR__ . '/vendors/Nette/nette.min.php';

require_once __DIR__ . '/src/interface/AutomatonFactory.php';
require_once __DIR__ . '/src/interface/Automaton.php';
require_once __DIR__ . '/src/interface/State.php';
require_once __DIR__ . '/src/interface/Transition.php';

require_once __DIR__ . '/src/exceptions/exceptions.php';

require_once __DIR__ . '/src/Automaton.php';
require_once __DIR__ . '/src/State.php';
require_once __DIR__ . '/src/Transition.php';

Nette\Diagnostics\Debugger::enable( Nette\Diagnostics\Debugger::DEVELOPMENT, __DIR__ . '/log' );

$A = new Automaton\State('A', FALSE, FALSE);
$B = new Automaton\State('B', FALSE, FALSE);
$C = new Automaton\State('C', FALSE, FALSE);

$states = array($A, $B, $C);
$alphabet = array('a', 'b', 'c');
$transitions = array(new Automaton\Transition($A, 'a', array($A, $B)));
$transitions = array(new Automaton\Transition($A, 'b', array($C)));
$transitions = array(new Automaton\Transition($A, 'c', array()));
$initials = array($B);
$finals = array($B, $C);

$automaton = new Automaton\Automaton($states, $alphabet, $transitions, $initials, $finals);
dump($automaton->B);
dump(isset($automaton->C));
dump(isset($automaton->D));
