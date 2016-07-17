<?php

namespace WouterJ\Peg;

use WouterJ\Peg\Handler;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class Grammar
{
    private $mainDefinitionId;
    /** @var Parser */
    private $parser;

    /**
     * @param string       $mainDefinitionId
     * @param Definition[] $definitions
     */
    public function __construct($mainDefinitionId, array $definitions)
    {
        $this->mainDefinitionId = $mainDefinitionId;

        $this->parser = new Parser($definitions);
    }

    public function parse($input)
    {
        $result = $this->parser->parse($this->mainDefinitionId, $input);

        if (!$result->isMatch()) {
            return null;
        }

        return $result->str();
    }
}
