<?php

/*
 * This file is part of the peg package.
 *
 * (c) 2016 Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WouterJ\Peg;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class GrammarTest extends \PHPUnit_Framework_TestCase
{
    public function testPredicates()
    {
        $grammar = new Grammar('Foobar', [
            new Definition('Foobar', ['sequence', [
                ['literal', 'foo'],
                ['and', ['literal', 'bar']],
            ]]),
        ]);

        $this->assertEquals('foo', $grammar->parse('foobar'));
        $this->assertNull($grammar->parse('foo'));
        $this->assertNull($grammar->parse('foobaz'));
    }

    public function testMatchFullInput()
    {
        $grammar = new Grammar('Line', [
            new Definition('Line', ['sequence', [
                ['repeat', ['literal', 'a'], 1],
                ['identifier', 'EndOfInput'],
            ]]),
            new Definition('EndOfInput', ['not', ['any']]),
        ]);

        $this->assertEquals('aaaaa', $grammar->parse('aaaaa'));
        $this->assertNull($grammar->parse('aaabc'));
    }

    public function testExampleFloat()
    {
        $grammar = new Grammar('Float', [
            new Definition('Float', ['sequence', [
                ['identifier', 'Digits'],
                ['literal', '.'],
                ['identifier', 'Digits'],
            ]]),
            new Definition('Digits', ['repeat', ['identifier', 'Digit'], 1]),
            new Definition('Digit', ['characterClass', '0-9']),
        ]);

        $this->assertEquals('1.2', $grammar->parse('1.2'));
        $this->assertEquals('1200.96', $grammar->parse('1200.96'));
        $this->assertNull($grammar->parse('ab.dc'));
        $this->assertNull($grammar->parse('1,2'));
    }

    public function testExampleSum()
    {
        $grammar = new Grammar('Sum', [
            new Definition('Sum', ['sequence', [
                ['identifier', 'Number'],
                ['repeat', ['identifier', 'Spacing'], 0, 1],
                ['literal', '+'],
                ['repeat', ['identifier', 'Spacing'], 0, 1],
                ['identifier', 'Number'],
            ]], function ($nested) {
                return array_sum(array_filter(Util::flattenArray($nested), 'is_float'));
            }),
            new Definition('Number', ['sequence', [
                ['repeat', ['identifier', 'Digit'], 1],
                ['repeat', ['sequence', [
                    ['literal', '.'],
                    ['repeat', ['identifier', 'Digit'], 1],
                ]], 0, 1],
            ]], function ($nested) {
                return (float) implode('', Util::flattenArray($nested));
            }),
            new Definition('Digit', ['characterClass', '0-9']),
            new Definition('Spacing', ['characterClass', '\s']),
        ]);

        $this->assertEquals(6, $grammar->parse('3 + 3'));
    }
}
