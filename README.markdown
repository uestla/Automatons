Automatons & grammars
=====================

This program provides you the simple way how to manipulate with automatons.

It can remove epsilon transitions, determinize, minimize and even normalize
any valid automaton you give as the input.

Let's see at some simple example:

```php

<?php

require_once __DIR__ . '/src/Automaton.php';

// first define the states and their transitions
$states = array(
	'A' => array(
		'a' => array('B'), // this means that we can go from state A to state B on letter 'a'
		'b' => array(), // we can't go anywhere from state A on letter 'b'
		'c' => array('C', 'A'), // multiple transitions are also supported
		'' => array(), // '' means epsilon
	),
	'B' => array(
		'a' => array('A'),
		'b' => array('A'),
		'c' => array('A', 'B'),
		'' => array('A'),
	),
	'C' => array(
		'a' => array('C'),
		'b' => array('C'),
		'c' => array('C'),
		'' => array(),
	),
);

// then we have to specify initial and final states
$initials = array('A');
$finals = array('A', 'C');

// that's all we need to create our automaton instance:
$automaton = new Automaton\Automaton($states, $initials, $finals);

echo $automaton;

```

Great. We've created our first automaton. Upper code will print following:

```

       \eps   a    b    c
><A      -    B    -   A|C
  B      A    A    A   A|B
 <C      -    C    C    C

```

Now let's take a look at some operations:

```php

<?php

$automaton->removeEpsilon();
/* result:
	            a      b      c
	><A         B      -     A|C
	  B        A|B     A    A|B|C
	 <C         C      C      C
*/



$automaton->determinize();
/* result:
	               a        b        c
	 <{A,B,C}   {A,B,C}   {A,C}   {A,B,C}
	 <{A,B}      {A,B}     {A}    {A,B,C}
	 <{A,C}      {B,C}     {C}     {A,C}
	><{A}         {B}      {}      {A,C}
	 <{B,C}     {A,B,C}   {A,C}   {A,B,C}
	  {B}        {A,B}     {A}    {A,B,C}
	 <{C}         {C}      {C}      {C}
	  {}          {}       {}       {}
*/



$automaton->minimize();
/* result:
	            a      b      c
	  I        III   IIIII  IIII
	  II       II     II     II
	 <III      III   IIIII  IIII
	 <IIII    IIII   IIII   IIII
	><IIIII     I     II    IIII
*/



$automaton->normalize();
/* result:
	      a  b  c
	><1   2  3  4
	  2   5  1  4
	  3   3  3  3
	 <4   4  4  4
	 <5   5  1  4
*/

```
