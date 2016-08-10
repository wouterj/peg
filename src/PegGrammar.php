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
 * A subclass of Grammar for the PEG syntax.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class PegGrammar extends Grammar
{
    public function __construct()
    {
        parent::__construct('Grammar', [
            // Grammar <- Spacing Definition+ EndOfFile
            new Definition('Grammar', ['sequence', [
                ['identifier', 'Spacing'],
                ['repeat', ['identifier', 'Definition'], 1],
                ['identifier', 'EndOfFile'],
            ]], function ($nested) {
                $definitions = $nested[1];

                return new Grammar($definitions[0]->identifier(), $definitions);
            }),
            // Definition <- Identifier LEFTARROW Expression
            new Definition('Definition', ['sequence', [
                ['identifier', 'Identifier'],
                ['identifier', 'LEFTARROW'],
                ['identifier', 'Expression'],
            ]], function ($nested) {
                $definitionId = $nested[0][1];
                $expression = $nested[2];

                return new Definition($definitionId, $expression);
            }),

            // Expression <- Sequence (SLASH Sequence)*
            new Definition('Expression', ['sequence', [
                ['identifier', 'Sequence'],
                ['repeat', ['sequence', [
                    ['identifier', 'SLASH'],
                    ['identifier', 'Sequence'],
                ]]],
            ]], function ($nested) {
                if (0 === count($nested[1])) {
                    return $nested[0];
                }

                return ['choice', array_merge([$nested[0]], array_map('next', $nested[1]))];
            }),
            // Sequence <- Prefix*
            new Definition('Sequence', ['repeat', ['identifier', 'Prefix']], function ($nested) {
                $prefixes = Util::nonNull($nested);
                if (1 === count($prefixes)) {
                    return $prefixes[0];
                }

                return ['sequence', $prefixes];
            }),
            // Prefix <- (AND / NOT)? Suffix
            new Definition('Prefix', ['sequence', [
                ['repeat', ['choice', [
                    ['identifier', 'AND'],
                    ['identifier', 'NOT'],
                ]], 0, 1],
                ['identifier', 'Suffix'],
            ]], function ($nested) {
                if ([] === $nested[0]) {
                    return $nested[1];
                }

                switch ($nested[0][0]) {
                    case '&':
                        return ['and', $nested[1]];

                    case '!':
                        return ['not', $nested[1]];
                }
            }),
            // Suffix <- Primary (QUESTION / STAR / PLUS)?
            new Definition('Suffix', ['sequence', [
                ['identifier', 'Primary'],
                ['repeat', ['choice', [
                    ['identifier', 'QUESTION'],
                    ['identifier', 'STAR'],
                    ['identifier', 'PLUS'],
                ]], 0, 1],
            ]], function ($nested) {
                if ([] === $nested[1]) {
                    return $nested[0];
                }

                switch (rtrim($nested[1][0])) {
                    case '?':
                        $min = 0;
                        $max = 1;

                        break;
                    case '*':
                        $min = 0;
                        $max = INF;

                        break;
                    case '+':
                        $min = 1;
                        $max = INF;

                        break;
                }

                return ['repeat', $nested[0], $min, $max];
            }),
            // Primary <- Identifier !LEFTARROW
            //          / OPEN Expression CLOSE
            //          / Literal / Class / DOT
            new Definition('Primary', ['choice', [
                ['sequence', [
                    ['identifier', 'Identifier'],
                    ['not', ['identifier', 'LEFTARROW']],
                ]],
                ['sequence', [
                    ['identifier', 'OPEN'],
                    ['identifier', 'Expression'],
                    ['identifier', 'CLOSE'],
                ]],
                ['identifier', 'Literal'],
                ['identifier', 'Class'],
                ['identifier', 'DOT'],
            ]], function ($nested) {
                if (is_string($nested[0]) && '.' === rtrim($nested[0])) {
                    return ['any'];
                }

                switch ($nested[0]) {
                    case 'characterClass':
                    case 'literal':
                        return $nested;

                    case '(':
                        return $nested[1];

                    default:
                        return $nested[0];
                }
            }),

            // Identifier <- IdentStart IdentCont* Spacing
            new Definition('Identifier', ['sequence', [
                ['identifier', 'IdentStart'],
                ['repeat', ['identifier', 'IdentCont']],
                ['identifier', 'Spacing'],
            ]], function ($nested) {
                return ['identifier', implode('', array_merge([$nested[0]], $nested[1]))];
            }),
            // IdentStart <- [a-zA-Z_]
            new Definition('IdentStart', ['characterClass', 'a-zA-Z_']),
            // IdentCont <- IdentStart / [0-9]
            new Definition('IdentCont', ['choice', [
                ['identifier', 'IdentStart'],
                ['characterClass', '0-9'],
            ]]),
            // Literal <- [’] (![’] Char)* [’] Spacing
            //          / ["] (!["] Char)* ["] Spacing
            new Definition('Literal', ['choice', [
                ['sequence', [
                    ['literal', '\''],
                    ['repeat', ['sequence', [
                        ['not', ['literal', '\'']],
                        ['identifier', 'Char'],
                    ]]],
                    ['literal', '\''],
                    ['identifier', 'Spacing'],
                ]],
                ['sequence', [
                    ['literal', '"'],
                    ['repeat', ['sequence', [
                        ['not', ['literal', '"']],
                        ['identifier', 'Char'],
                    ]]],
                    ['literal', '"'],
                    ['identifier', 'Spacing'],
                ]],
            ]], function ($nested) {
                return ['literal', implode('', array_map('next', $nested[1]))];
            }),
            // Class <- ’[’ (!’]’ Range)* ’]’ Spacing
            new Definition('Class', ['sequence', [
                ['literal', '['],
                ['repeat', ['sequence', [
                    ['not', ['literal', ']']],
                    ['identifier', 'Range'],
                ]]],
                ['literal', ']'],
                ['identifier', 'Spacing'],
            ]], function ($nested) {
                $characters = implode('', array_filter(Util::flattenArray($nested[1]), function ($v) { return $v !== null; }));

                return ['characterClass', $characters];
            }),
            // Range <- Char ’-’ Char / Char
            new Definition('Range', ['choice', [
                ['sequence', [
                    ['identifier', 'Char'],
                    ['literal', '-'],
                    ['identifier', 'Char'],
                ]],
                ['identifier', 'Char'],
            ]]),
            // Char <- ’\\’ [nrt’"\[\]\\]
            //       / ’\\’ [0-2][0-7][0-7]
            //       / ’\\’ [0-7][0-7]?
            //       / !’\\’ .
            new Definition('Char', ['choice', [
                ['sequence', [
                    ['literal', '\\'],
                    ['characterClass', 'nrt\'"\[\]\\\\'],
                ]],
                ['sequence', [
                    ['literal', '\\'],
                    ['sequence', [
                        ['characterClass', '0-2'],
                        ['characterClass', '0-7'],
                        ['characterClass', '0-7'],
                    ]],
                ]],
                ['sequence', [
                    ['literal', '\\'],
                    ['sequence', [
                        ['characterClass', '0-7'],
                        ['repeat', ['characterClass', '0-7'], 0, 1],
                    ]],
                ]],
                ['sequence', [
                    ['not', ['literal', '\\']],
                    ['any'],
                ]],
            ]]),

            // LEFTARROW <- ’<-’ Spacing
            new Definition('LEFTARROW', ['sequence', [
                ['literal', '<-'],
                ['identifier', 'Spacing'],
            ]]),
            // SLASH <- ’/’ Spacing
            new Definition('SLASH', ['sequence', [
                ['literal', '/'],
                ['identifier', 'Spacing'],
            ]]),
            // AND <- ’&’ Spacing
            new Definition('AND', ['sequence', [
                ['literal', '&'],
                ['identifier', 'Spacing'],
            ]]),
            // NOT <- ’!’ Spacing
            new Definition('NOT', ['sequence', [
                ['literal', '!'],
                ['identifier', 'Spacing'],
            ]]),
            // QUESTION <- ’?’ Spacing
            new Definition('QUESTION', ['sequence', [
                ['literal', '?'],
                ['identifier', 'Spacing'],
            ]]),
            // STAR <- ’*’ Spacing
            new Definition('STAR', ['sequence', [
                ['literal', '*'],
                ['identifier', 'Spacing'],
            ]]),
            // PLUS <- ’+’ Spacing
            new Definition('PLUS', ['sequence', [
                ['literal', '+'],
                ['identifier', 'Spacing'],
            ]]),
            // OPEN <- ’(’ Spacing
            new Definition('OPEN', ['sequence', [
                ['literal', '('],
                ['identifier', 'Spacing'],
            ]]),
            // CLOSE <- ’)’ Spacing
            new Definition('CLOSE', ['sequence', [
                ['literal', ')'],
                ['identifier', 'Spacing'],
            ]]),
            // DOT <- ’.’ Spacing
            new Definition('DOT', ['sequence', [
                ['literal', '.'],
                ['identifier', 'Spacing'],
            ]]),

            // Spacing <- (Space / Comment)*
            new Definition('Spacing', ['repeat', [
                'choice', [
                    ['identifier', 'Space'],
                    ['identifier', 'Comment'],
                ],
            ]]),
            // Comment <- ’#’ (!EndOfLine .)* EndOfLine
            new Definition('Comment', ['sequence', [
                ['literal', '#'],
                ['repeat', ['sequence', [
                    ['not', ['identifier', 'EndOfLine']],
                    ['any'],
                ]]],
                ['identifier', 'EndOfLine'],
            ]]),
            // Space <- ’ ’ / ’\t’ / EndOfLine
            new Definition('Space', ['choice', [
                ['literal', ' '],
                ['literal', "\t"],
                ['identifier', 'EndOfLine'],
            ]]),
            // EndOfLine <- ’\r\n’ / ’\n’ / ’\r’
            new Definition('EndOfLine', ['choice', [
                ['literal', "\r\n"],
                ['literal', "\n"],
                ['literal', "\r"],
            ]]),
            // EndOfFile <- !.
            new Definition('EndOfFile', ['not', ['any']]),
        ]);
    }
}
