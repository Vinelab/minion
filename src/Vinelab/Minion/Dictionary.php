<?php

namespace Vinelab\Minion;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Dictionary
{
    public function __construct(array $attributes)
    {
        $this->dictionary = json_decode(json_encode($attributes));
    }

    /**
     * Get a new dictionary with the given attributes.
     *
     * @param mixed $attributes
     *
     * @return \Vinelab\Minion\Dictionary
     */
    public static function make($attributes)
    {
        if (!is_array($attributes)) {
            $attributes = (array) $attributes;
        }

        return new static($attributes);
    }

    /**
     * Get the array representation of this dictionary.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toKeyValue((array) $this->dictionary);
    }

    /**
     * Transform the given array of values into a key-value pair recursively.
     *
     * @param array $values
     *
     * @return array
     */
    public function toKeyValue(array $values)
    {
        $result = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->toKeyValue($value);
            } elseif (is_object($value)) {
                $result[$key] = $this->toKeyValue((array) $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Override the magic __get to reach the attribtues of this dictionary.
     *
     * @param string $attribute
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        if (isset($this->dictionary->$attribute)) {
            return $this->dictionary->$attribute;
        }
    }

    /**
     * Override to support calling isset() on attributes of this dictionary.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function __isset($attribute)
    {
        return isset($this->dictionary->$attribute);
    }
}
