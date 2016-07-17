<?php

namespace WouterJ\Peg;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class Result
{
    private $value;
    private $length;
    private $offset;
    private $match = true;

    /**
     * @param int   $length The length of the match (used to determine new offset)
     * @param mixed $value  The value of the definition
     * @param int   $offset The start offset
     *
     * @return Result
     */
    public static function match($length, $value, $offset)
    {
        $result = new self();
        $result->length = $length;
        $result->value = $value;
        $result->offset = $offset;

        return $result;
    }

    public static function noMatch($offset)
    {
        $match = new self();
        $match->offset = $offset;
        $match->match = false;

        return $match;
    }

    public function offset()
    {
        return $this->offset;
    }

    public function length()
    {
        return $this->length;
    }

    public function newOffset()
    {
        return $this->offset + $this->length;
    }

    public function isMatch()
    {
        return $this->match;
    }

    public function value()
    {
        return $this->value;
    }
}
