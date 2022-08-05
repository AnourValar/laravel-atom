<?php

namespace AnourValar\LaravelAtom;

abstract class AbstractMapper implements \JsonSerializable
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @return array
     */
    abstract protected function getScheme(): array;

    /**
     * @param array $attributes
     * @throws \LogicException
     */
    public function __construct(array $attributes)
    {
        foreach ($this->getScheme() as $attribute => $cast) {
            if (! isset($attributes[$attribute]) && stripos((string) $cast, '?') === 0) {
                $attributes[$attribute] = null;
            }

            if (! array_key_exists($attribute, $attributes)) {
                throw new \LogicException("Attribute [$attribute] required.");
            }

            if ($attributes[$attribute] instanceof self) {
                $attributes[$attribute] = $attributes[$attribute]->toArray();
            }

            if ($cast) {
                if (stripos($cast, '?') === 0) {
                    if (is_null($attributes[$attribute])) {
                        $cast = null;
                    } else {
                        $cast = mb_substr($cast, 1);
                    }
                }

                if ($cast) {
                    settype($attributes[$attribute], $cast);
                }
            }

            $this->attributes[$attribute] = $attributes[$attribute];
            unset($attributes[$attribute]);
        }

        if (count($attributes)) {
            throw new \LogicException('Invalid attributes: '.implode(', ', array_keys($attributes)));
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get data (filtered)
     *
     * @param mixed $keys
     * @return array
     */
    public function only($keys): array
    {
        $keys = (array) $keys;

        return array_filter(
            $this->attributes,
            function ($key) use ($keys) {
                return in_array($key, $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @see \JsonSerializable
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->attributes;
    }
}
