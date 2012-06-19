<?php

require_once __DIR__ . '/nette.min.php';
require_once __DIR__ . '/src/Automaton.php';
require_once __DIR__ . '/src/factories/IFactory.php';
require_once __DIR__ . '/src/factories/FileFactory.php';

Nette\Diagnostics\Debugger::enable( Nette\Diagnostics\Debugger::DEVELOPMENT );
Nette\Diagnostics\Debugger::$maxDepth = FALSE;
Nette\Diagnostics\Debugger::$strictMode = TRUE;

$factory = new Automaton\FileFactory(__DIR__ . '/automaton.txt');
$automaton = $factory->create()->determinize()->minimize()->normalize();
dump($automaton);
