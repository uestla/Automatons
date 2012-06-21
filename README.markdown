Automatons & grammars
=====================

This program provides you the simple way to manipulate with automatons.

It can remove epsilon transitions, determinize, minimize and even normalize
any valid automaton you give as the input.



Automaton definition
--------------------

Let's take a look at some simple example:

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



External file definition
------------------------

But let's be honest - the PHP definition is not that handy, you have to write too much.

Don't worry, you can specify the automaton in an external file.

Let's see the upper automaton described in a file format:

```
     a  b  c   \eps
><A  B  -  C|A  -
  B   A  A  A|B  A
 <C   C  C  C    -
```

Whole file has obviously a table-like structure. As you can see, the first line specifies the
automaton alphabet. Epsilon character is written as `\eps`.

Every next line specifies state and its transitions. The state name can be preffixed with `>`, `<` or both
(no whitespace between the symbol and the state name!). The `>` symbol means the state is initial, the `<` one
means final. Empty transition target is written as a `-` symbol and multiple targets are joined with `|`
(no whitespace again!).

Now we save this file as `automaton.txt`. Now let's take a look at the PHP code needed
for this automaton to create:

```php

<?php

require_once __DIR__ . '/src/factories/FileFactory.php';

$factory = new Automaton\FileFactory(__DIR__ . '/automaton.txt');
$automaton = $factory->create(); // scans the file and creates the automaton instance
$automaton->determinize()->minimize()->normalize();
echo $automaton;

/* result (same as the one above):
	      a  b  c
	><1   2  3  4
	  2   5  1  4
	  3   3  3  3
	 <4   4  4  4
	 <5   5  1  4
*/

```

Enjoy!
