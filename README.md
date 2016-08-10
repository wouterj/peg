PHP PEG Parser
==============

PEG is a generic [PEG parser](http://bford.info/packrat/) written in PHP. This
parser allows you to write parsers using the Parsing Expression Grammar.

Installation
------------

Install PEG using [Composer](https://getcomposer.org/download/):

```bash
$ composer require wouterj/peg
```

Usage
-----

Use the `Grammar` class to specify the Parsing Expression Grammar by creating
`Definition` instances:

```php
use WouterJ\Peg\Grammar;
use WouterJ\Peg\Definition;

// specifies that Float is the main definition
$grammar = new Grammar('Float', [
    // matches any Digits (next definition), followed by a . (dot) and any Digits
    new Definition('Float', ['sequence', [
        ['identifier', 'Digits'],
        ['literal', '.'],
        ['identifier', 'Digits'],
    ]]),

    // matches any Digit (next definition) one or more times
    new Definition('Digits', ['repeat', ['identifier', 'Digit'], 1]),

    // matches 0, 1, 2, 3, 4, 5, 6, 7, 8 and 9
    new Definition('Digit', ['characterClass', '0-9']),
]);
```

Use `Grammar#parse()` to parse input strings using this grammar. The return value
is the part of the string that matched or `null` when there is no match:

```php
// ...

echo $grammar->parse('1.2'); // 1.2
echo $grammar->parse('1039.50'); // 1039.50
echo $grammar->parse('abc'); // NULL

// please note that it doesn't have to match the full string
echo $grammar->parse('1.2a'); // 1.2
```

### Using the PEG syntax

Of course, using arrays to define the grammar is really ugly. This is why PEG
comes with `PegGrammar`, a parser that is able to parse PEG syntax. It returns
a ready to use grammar instance using the parsed grammar.

```php
use WouterJ\Peg\PegGrammar;

$pegGrammar = new PegGrammar();

// our float parser (equal to the previous one)
$grammar = $pegGrammar->parse(<<<EOG
Float  <- Digits '.' Digits
Digits <- Digit+
Digit  <- [0-9]
EOG
);

$grammar->parse('1039.50'); // 1039.50
```

For information about the PEG syntax, see
[Parsing Expression Grammars: A Recognition-Based Syntactic Foundation](http://bford.info/pub/lang/peg.pdf)
by Bryan Ford.

License
-------

The project is released under the BSD-3 Clause license. For the full copyright
and license information, please view the LICENSE file that was distributed with
this source code.

Contributing
------------

Whether it's just a reference, online high-five, bug report or feature proposal,
all contributions are welcome. The main project is located at [GitHub](http://github.com/wouterj/peg).
