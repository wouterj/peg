<?php

namespace WouterJ\Peg;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class Result
{
    private $str;
    private $offset;

    public static function match($str, $offset)
    {
        $result = new self();
        $result->str = $str;
        $result->offset = $offset;

        return $result;
    }

    public static function noMatch($offset)
    {
        $match = new self();
        $match->offset = $offset;

        return $match;
    }

    public function newOffset()
    {
        return $this->offset + strlen($this->str);
    }

    public function isMatch()
    {
        return null !== $this->str;
    }

    public function str()
    {
        return $this->str;
    }
}
