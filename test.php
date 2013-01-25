<?php

require_once __DIR__ . '/src/factories/FileFactory.php';

$factory = new Automaton\FileFactory;
echo $factory->create( __DIR__ . '/automaton.txt' )->determinize()->minimize()->normalize();
