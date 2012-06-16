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
$alphabet = array('a', 'b', 'c', '');

$transitions = array();
$transitions[] = new Automaton\Transition($A, 'a', array($B));
$transitions[] = new Automaton\Transition($A, 'b', array());
$transitions[] = new Automaton\Transition($A, 'c', array($C, $A));
$transitions[] = new Automaton\Transition($A, '', array());
$transitions[] = new Automaton\Transition($B, 'a', array($A));
$transitions[] = new Automaton\Transition($B, 'b', array($A));
$transitions[] = new Automaton\Transition($B, 'c', array($A, $B));
$transitions[] = new Automaton\Transition($B, '', array($A));
$transitions[] = new Automaton\Transition($C, 'a', array($C));
$transitions[] = new Automaton\Transition($C, 'b', array($C));
$transitions[] = new Automaton\Transition($C, 'c', array($C));
$transitions[] = new Automaton\Transition($C, '', array());

$initials = array($A);
$finals = array($A, $C);

$automaton = new Automaton\Automaton($states, $alphabet, $transitions, $initials, $finals);
$automaton->determinize();

echo "Success.\n";
