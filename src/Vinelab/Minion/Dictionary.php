<?php namespace Vinelab\Minion;

/**
 * @author Abed Halawi <abed.halawi@vinelab.com>
 */
class Dictionary {

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get a new dictionary with the given attributes.
     *
     * @param  mixed $attributes
     *
     * @return \Vinelab\Minion\Dictionary
     */
    public static function make($attributes)
    {
        if (! is_array($attributes)) {
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
        return $this->attributes;
    }

    /**
     * Override the magic __get to reach the attribtues of this dictionary.
     *
     * @param  string $attribute
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        if (isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        }
    }

    /**
     * Override to support calling isset() on attributes of this dictionary.
     *
     * @param  string  $attribute
     *
     * @return boolean
     */
    public function __isset($attribute)
    {
        return isset($this->attributes[$attribute]);
    }
}
