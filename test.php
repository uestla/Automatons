<?php

require_once __DIR__ . '/src/factories/FileFactory.php';

$factory = new Automaton\FileFactory(__DIR__ . '/automaton.txt');
$automaton = $factory->create();
$automaton->determinize()->minimize()->normalize();
echo $automaton;
