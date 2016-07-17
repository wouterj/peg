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
    /** @var null|callable */
    private $action;

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

    public function call($value)
    {
        $value = (array) $value;

        if ($this->action) {
            return call_user_func($this->action, $value);
        }

        return is_array($value) ? implode('', Util::flattenArray($value)) : $value;
    }
}
