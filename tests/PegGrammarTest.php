<?php

namespace WouterJ\Peg;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class PegGrammarTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider getGrammars */
    public function testGrammar($filePath)
    {
        $grammar = new PegGrammar();

        $parsedGrammar = $grammar->parse(file_get_contents($filePath));
        $this->assertInstanceOf(Grammar::class, $parsedGrammar);
        $this->assertEquals('A nice sentence!', $parsedGrammar->parse('A nice sentence!'));
    }

    public function getGrammars()
    {
        return [
            //[__DIR__.'/fixtures/example1.peg'],
            [__DIR__.'/fixtures/simple.peg'],
        ];
    }
}
