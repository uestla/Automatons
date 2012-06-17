<?php

require_once __DIR__ . '/src/Automaton.php';
require_once __DIR__ . '/src/factories/IFactory.php';
require_once __DIR__ . '/src/factories/FileFactory.php';

$factory = new Automaton\FileFactory(__DIR__ . '/automaton.txt');
$automaton = $factory->create();
dump($automaton);
