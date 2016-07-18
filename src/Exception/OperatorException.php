<?php

namespace WouterJ\Peg\Exception;

use WouterJ\Peg\Exception;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class OperatorException extends \LogicException implements Exception
{
    public static function undefined($operatorName)
    {
        return new self(sprintf('Undefined operator `%s`.', $operatorName));
    }
}
