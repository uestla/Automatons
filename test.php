<?php

require_once __DIR__ . '/vendors/Nette/nette.min.php';

require_once __DIR__ . '/interface/IAutomaton.php';
require_once __DIR__ . '/interface/IAutomatonFactory.php';
require_once __DIR__ . '/interface/IState.php';

require_once __DIR__ . '/Automaton.php';
require_once __DIR__ . '/State.php';

Nette\Diagnostics\Debugger::enable();

echo "Hello world.\n";
