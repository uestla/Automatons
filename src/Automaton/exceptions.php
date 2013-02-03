<?php

/**
 * This file is part of the Automaton package
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/Automatons
 */


namespace Automaton;


class InvalidInputException extends \Exception {}
class InvalidStateSetException extends InvalidInputException {}
class InvalidAlphabetException extends InvalidInputException {}
class StateNotFoundException extends InvalidInputException {}
class SymbolNotFoundException extends InvalidInputException {}
class InvalidTargetCount extends InvalidInputException {}
