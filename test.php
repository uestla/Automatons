<?php

require_once __DIR__ . '/vendors/Nette/nette.min.php';

require_once __DIR__ . '/src/interface/IAutomaton.php';
require_once __DIR__ . '/src/interface/IAutomatonFactory.php';
require_once __DIR__ . '/src/interface/IState.php';

require_once __DIR__ . '/src/Automaton.php';
require_once __DIR__ . '/src/State.php';

Nette\Diagnostics\Debugger::enable( Nette\Diagnostics\Debugger::DEVELOPMENT );

$states = array(
	new Automaton\State('A', FALSE, FALSE),
	new Automaton\State('B', FALSE, FALSE),
	new Automaton\State('C', FALSE, FALSE),
);

$automaton = new Automaton\Automaton($states);
dump($automaton->B);
