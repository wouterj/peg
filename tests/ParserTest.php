<?php

namespace WouterJ\Peg;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testLiteral()
    {
        $parser = new Parser([
            new Definition('Letter', ['literal', 'a']),
        ]);

        $r = $parser->parse('Letter', 'a');
        $this->assertEquals('a', $r->str());
        $this->assertEquals(1, $r->newOffset());
        $this->assertFalse($parser->parse('Letter', 'b')->isMatch());
    }

    public function testIdentifier()
    {
        $parser = new Parser([
            new Definition('SingleLetterWord', ['identifier', 'Letter']),
            new Definition('Letter', ['literal', 'a']),
        ]);

        $r = $parser->parse('SingleLetterWord', 'a');
        $this->assertEquals('a', $r->str());
        $this->assertEquals(1, $r->newOffset());
        $this->assertFalse($parser->parse('SingleLetterWord', 'b')->isMatch());
    }

    public function testRepeat()
    {
        $parser = new Parser([
            new Definition('Word', ['repeat', ['literal', 'a']]),
        ]);

        $this->assertEquals('a', $parser->parse('Word', 'a')->str());
        $this->assertEquals('aaaa', $parser->parse('Word', 'aaaa')->str());

        $r = $parser->parse('Word', 'aabc');
        $this->assertEquals('aa', $r->str());
        $this->assertEquals(2, $r->newOffset());

        $r = $parser->parse('Word', 'bcaa');
        $this->assertEquals('', $r->str());
        $this->assertEquals(0, $r->newOffset());
    }

    public function testRepeatWithMinAndMax()
    {
        $parser = new Parser([
            new Definition('Word', ['repeat', ['literal', 'a'], 2, 4]),
        ]);

        $this->assertFalse($parser->parse('Word', 'a')->isMatch());
        $this->assertEquals('aa', $parser->parse('Word', 'aa')->str());

        $r = $parser->parse('Word', 'aaa');
        $this->assertEquals('aaa', $r->str());
        $this->assertEquals(3, $r->newOffset());

        $this->assertEquals('aaaa', $parser->parse('Word', 'aaaa')->str());

        $r = $parser->parse('Word', 'aaaaa');
        $this->assertEquals('aaaa', $r->str());
        $this->assertEquals(4, $r->newOffset());
    }

    public function testCharacterClass()
    {
        $parser = new Parser([
            new Definition('Digit', ['characterClass', '0-9']),
        ]);

        $this->assertEquals('3', $parser->parse('Digit', '3')->str());

        $r = $parser->parse('Digit', '9');
        $this->assertEquals('9', $r->str());
        $this->assertEquals(1, $r->newOffset());

        $this->assertFalse($parser->parse('Digit', 'a')->isMatch());
    }

    public function testSequence()
    {
        $parser = new Parser([
            new Definition('Sum', ['sequence', [
                ['identifier', 'Int'],
                ['literal', '+'],
                ['identifier', 'Int'],
            ]]),
            new Definition('Int', ['characterClass', '0-9']),
        ]);

        $r = $parser->parse('Sum', '3+3');
        $this->assertEquals('3+3', $r->str());
        $this->assertEquals(3, $r->newOffset());

        $this->assertFalse($parser->parse('Sum', '3-5')->isMatch());
        $this->assertFalse($parser->parse('Sum', '35-')->isMatch());
    }

    public function testChoice()
    {
        $parser = new Parser([
            new Definition('OneOrTwo', ['choice', [
                ['literal', '1'],
                ['literal', '2'],
            ]]),
        ]);

        $this->assertEquals('1', $parser->parse('OneOrTwo', '1')->str());

        $r = $parser->parse('OneOrTwo', '2');
        $this->assertEquals('2', $r->str());
        $this->assertEquals(1, $r->newOffset());

        $this->assertFalse($parser->parse('OneOrTwo', '3')->isMatch());
    }

    public function testAny()
    {
        $parser = new Parser([
            new Definition('Everything', ['any']),
        ]);

        $this->assertEquals('1', $parser->parse('Everything', '1')->str());
        $this->assertEquals('a', $parser->parse('Everything', 'a')->str());

        $r = $parser->parse('Everything', '?');
        $this->assertEquals('?', $r->str());
        $this->assertEquals(1, $r->newOffset());
    }

    public function testNot()
    {
        $parser = new Parser([
            new Definition('NonWord', ['not', ['characterClass', 'a-zA-Z']]),
        ]);

        $this->assertTrue($parser->parse('NonWord', '1')->isMatch());

        $r = $parser->parse('NonWord', '#');
        $this->assertTrue($r->isMatch());
        $this->assertEquals(0, $r->newOffset());

        $this->assertFalse($parser->parse('NonWord', 'a')->isMatch());
    }

    public function testAnd()
    {
        $parser = new Parser([
            new Definition('LetterA', ['and', ['literal', 'a']]),
        ]);

        $r = $parser->parse('LetterA', 'a');
        $this->assertTrue($r->isMatch());
        $this->assertEquals(0, $r->newOffset());

        $this->assertFalse($parser->parse('LetterA', 'b')->isMatch());
    }
}
