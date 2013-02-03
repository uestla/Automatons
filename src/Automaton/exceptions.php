<?php

namespace Automaton;


class InvalidInputException extends \Exception {}
class InvalidStateSetException extends InvalidInputException {}
class InvalidAlphabetException extends InvalidInputException {}
class StateNotFoundException extends InvalidInputException {}
class SymbolNotFoundException extends InvalidInputException {}
class InvalidTargetCount extends InvalidInputException {}
