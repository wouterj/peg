<?php

namespace WouterJ\Peg\Exception;

use WouterJ\Peg\Definition;
use WouterJ\Peg\Exception;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class DefinitionException extends \LogicException implements Exception
{
    /**
     * @param string   $identifier
     * @param string[] $availableDefinitions
     *
     * @return self
     */
    public static function undefined($identifier, array $availableDefinitions = [])
    {
        $candidates = array();
        foreach ($availableDefinitions as $definitionId) {
            if (
                false !== strpos($definitionId, $identifier)
                || levenshtein($identifier, $definitionId) <= strlen($identifier) / 3
            ) {
                $candidates[] = $definitionId;
            }
        }

        return new self(sprintf(
            'Unknown definition `%s`%s',
            $identifier,
            0 === count($candidates) ? '.' : ', did you mean one of these `'.implode('`, `', $candidates).'`?'
        ));
    }

    public static function invalid($identifier, \Exception $previous)
    {
        return new self(
            sprintf('Invalid definition `%s`: %s', $identifier, $previous->getMessage()),
            0,
            $previous
        );
    }
}
