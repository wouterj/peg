<?php

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
}
