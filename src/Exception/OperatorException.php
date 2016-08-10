<?php

/*
 * This file is part of the peg package.
 *
 * (c) 2016 Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
