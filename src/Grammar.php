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
 * The main entry point of the Peg library.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class Grammar
{
    private $mainDefinitionId;
    /** @var Parser */
    private $parser;

    /**
     * @param string       $mainDefinitionId The definition to use as top-level
     * @param Definition[] $definitions
     */
    public function __construct($mainDefinitionId, array $definitions)
    {
        $this->mainDefinitionId = $mainDefinitionId;

        $this->parser = new Parser($definitions);
    }

    /**
     * Parses the input string using the defined grammar.
     *
     * The return value is one of:
     *   `null`   there is no match
     *   `string` the consumed part of the string
     *   `mixed`  the value returned by the PEG actions defined in the grammar
     *
     * @param string $input
     *
     * @return null|string|mixed
     */
    public function parse($input)
    {
        $result = $this->parser->parse($this->mainDefinitionId, $input);

        if (!$result->isMatch()) {
            return null;
        }

        return $result->value();
    }
}
