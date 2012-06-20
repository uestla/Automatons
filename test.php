<?php

require_once __DIR__ . '/src/Automaton.php';
require_once __DIR__ . '/src/factories/IFactory.php';
require_once __DIR__ . '/src/factories/FileFactory.php';
require_once __DIR__ . '/src/renderers/IRenderer.php';
require_once __DIR__ . '/src/renderers/DefaultRenderer.php';

$factory = new Automaton\FileFactory(__DIR__ . '/automaton.txt');
$automaton = $factory->create()->determinize()->minimize()->normalize();
echo $automaton;
