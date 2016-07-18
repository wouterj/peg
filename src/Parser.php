<?php

namespace WouterJ\Peg;

use WouterJ\Peg\Exception\DefinitionException;
use WouterJ\Peg\Exception\OperatorException;

/**
 * The parser of input strings.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class Parser
{
    /** @var Definition[] */
    private $definitions;

    /**
     * @param Definition[] $definitions
     */
    public function __construct(array $definitions)
    {
        foreach ($definitions as $definition) {
            $this->definitions[$definition->identifier()] = $definition;
        }
    }

    /**
     * Parses using a definition.
     *
     * @param string $definitionId
     * @param string $input
     * @param int    $offset       Current position in the $input
     *
     * @return Result
     *
     * @throws \LogicException When the definition is invalid.
     */
    public function parse($definitionId, $input, $offset = 0)
    {
        if (!isset($this->definitions[$definitionId])) {
            throw DefinitionException::undefined($definitionId, array_keys($this->definitions));
        }
        $definition = $this->definitions[$definitionId];

        try {
            $result = $this->parseOperator($definition->rule(), $input, $offset);

            if (!$result->isMatch()) {
                return $result;
            }

            return Result::match($result->length(), $definition->call($result->value()), $result->offset());
        } catch (OperatorException $e) {
            throw DefinitionException::invalid($definitionId, $e);
        }
    }

    /**
     * Parses using an operator.
     *
     * @param array  $operator First element is the operator name, other elements operator values
     * @param string $input
     * @param int    $offset   Current position in the input string
     *
     * @return Result
     *
     * @throws \LogicException When the operator is not known
     */
    private function parseOperator($operator, $input, $offset)
    {
        $method = 'parse'.ucfirst($operator[0]);
        if (method_exists($this, $method)) {
            return $this->$method($operator, $input, $offset);
        }

        throw OperatorException::undefined($operator[0]);
    }

    /**
     * Parses a literal operator.
     *
     * Arguments:
     *   1) The literal string
     *
     * PEG syntax:
     *   ' '
     *   " "
     */
    private function parseLiteral($operator, $input, $offset)
    {
        if (substr($input, $offset, strlen($operator[1])) === $operator[1]) {
            return Result::match(strlen($operator[1]), $operator[1], $offset);
        }

        return Result::noMatch($offset);
    }

    /**
     * Parses a definition identifier.
     *
     * Arguments:
     *   1) The definition ID
     *
     * PEG syntax:
     *   e
     */
    private function parseIdentifier($operator, $input, $offset)
    {
        return $this->parse($operator[1], $input, $offset);
    }

    /**
     * Parses a repeat group.
     *
     * Arguments:
     *   1) The group to repeat
     *   2) The minimum number of iterations (default = 0)
     *   3) The maximum number of iterations (default = INF)
     *
     * PEG syntax:
     *   e+
     *   e*
     *   e?
     */
    private function parseRepeat($operator, $input, $offset)
    {
        $_offset = $offset;
        $childOperator = $operator[1];
        $min = $operator[2] ?? 0;
        $max = $operator[3] ?? INF;
        $matches = [];
        $matchLen = 0;
        $inputLen = strlen($input);

        $i = 0;
        while (++$i <= $max) {
            $result = $this->parseOperator($childOperator, $input, $offset);

            $offset = $result->newOffset();
            if (!$result->isMatch() || $offset > $inputLen) {
                if ($i <= $min) {
                    return Result::noMatch($_offset);
                }

                break;
            }
            $matches[] = $result->value();
            $matchLen += $result->length();
        }

        return Result::match($matchLen, $matches, $_offset);
    }

    /**
     * Parses a character class.
     *
     * Arguments:
     *   1) The range(s)
     *
     * PEG syntax:
     *   [ ]
     */
    private function parseCharacterClass($operator, $input, $offset)
    {
        $regex = '{^['.$operator[1].']}';

        if (preg_match($regex, substr($input, $offset), $match)) {
            return Result::match(1, $match[0], $offset);
        }

        return Result::noMatch($offset);
    }

    /**
     * Parses a sequence.
     *
     * Arguments:
     *   1) The sequence (array of operators)
     *
     * PEG syntax:
     *   e1 e2
     */
    private function parseSequence($operator, $input, $offset)
    {
        $_offset = $offset;
        $sequence = $operator[1];
        $matches = [];
        $matchLen = 0;

        foreach ($sequence as $operator) {
            $result = $this->parseOperator($operator, $input, $offset);

            if (!$result->isMatch()) {
                return Result::noMatch($_offset);
            }

            $offset = $result->newOffset();
            $matches[] = $result->value();
            $matchLen += $result->length();
        }

        return Result::match($matchLen, $matches, $_offset);
    }

    /**
     * Parses a prioritized choice.
     *
     * Arguments:
     *   1) The prioritized list of options
     *
     * PEG syntax:
     *   e1 / e2
     */
    private function parseChoice($operator, $input, $offset)
    {
        $operators = $operator[1];

        foreach ($operators as $operator) {
            $result = $this->parseOperator($operator, $input, $offset);

            if ($result->isMatch()) {
                return $result;
            }
        }

        return Result::noMatch($offset);
    }

    /**
     * Parses the any operator.
     *
     * Arguments:
     *   -
     *
     * PEG syntax:
     *   .
     */
    private function parseAny($operator, $input, $offset)
    {
        if ((strlen($input) - $offset) >= 1) {
            return Result::match(1, substr($input, $offset, 1), $offset);
        }

        return Result::noMatch($offset);
    }

    /**
     * Parses the not precedent.
     *
     * Arguments
     *   1) The expression to not match
     *
     * PEG syntax
     *   !e
     */
    private function parseNot($operator, $input, $offset)
    {
        $result = $this->parseOperator($operator[1], $input, $offset);

        if ($result->isMatch()) {
            return Result::noMatch($offset);
        }

        return Result::match(0, null, $offset);
    }

    /**
     * Parses the and precedent.
     *
     * Arguments:
     *   1) The expression to match
     *
     * PEG syntax
     *   &e
     */
    private function parseAnd($operator, $input, $offset)
    {
        $result = $this->parseOperator($operator[1], $input, $offset);

        if ($result->isMatch()) {
            return Result::match(0, null, $offset);
        }

        return Result::noMatch($offset);
    }
}
