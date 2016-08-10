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
 * A single type definition.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
final class Definition
{
    private $id;
    /** @var array */
    private $rule;
    /** @var null|callable */
    private $action;

    /**
     * @param string        $id
     * @param array         $rule
     * @param null|callable $action Transforms the result to a custom value
     */
    public function __construct($id, array $rule, $action = null)
    {
        $this->id = $id;
        $this->rule = $rule;
        $this->action = $action;
    }

    public function identifier()
    {
        return $this->id;
    }

    public function rule()
    {
        return $this->rule;
    }

    /**
     * Parses the value matched by this definition.
     *
     * If no action was provided, this will flatten the nested
     * input array and return it as a string.
     *
     * Otherwise, the action will be called with the nested
     * input array as argument.
     *
     * @param mixed $value
     *
     * @return string|mixed
     */
    public function call($value)
    {
        $_value = (array) $value;

        if ($this->action) {
            return call_user_func($this->action, $_value);
        }

        return is_array($value) ? implode('', Util::flattenArray($value)) : $value;
    }
}
