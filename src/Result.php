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
 * This class is returned from all parse* methods in the Parser.
 *
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
     * @return self
     */
    public static function match($length, $value, $offset)
    {
        $result = new self();
        $result->length = $length;
        $result->value = $value;
        $result->offset = $offset;

        return $result;
    }

    /**
     * @param int $offset The start offset
     *
     * @return self
     */
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

    public function newOffset()
    {
        return $this->offset + $this->length;
    }

    public function length()
    {
        return $this->length;
    }

    public function value()
    {
        return $this->value;
    }

    public function isMatch()
    {
        return $this->match;
    }
}
