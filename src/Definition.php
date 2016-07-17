<?php

namespace WouterJ\Peg;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class Definition
{
    private $id;
    /** @var array */
    private $rule;

    public function __construct($id, array $rule)
    {
        $this->id = $id;
        $this->rule = $rule;
    }

    public function identifier()
    {
        return $this->id;
    }

    public function rule()
    {
        return $this->rule;
    }
}
