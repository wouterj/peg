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
        $originalValue = [$value];
        if (is_array($value)) {
            $originalValue = $value;
            $value = static::flattenArray($value);
        }

        if ($this->action) {
            return call_user_func($this->action, $value, $originalValue);
        }

        return is_array($value) ? implode('', $value) : $value;
    }

    private static function flattenArray(array $array)
    {
        $result = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                $result = array_merge($result, static::flattenArray($item));

                continue;
            }

            $result[] = $item;
        }

        return $result;
    }
}
