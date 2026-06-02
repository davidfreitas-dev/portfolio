<?php

declare(strict_types=1);

namespace App\Domain\Model;

abstract class Model
{
    private array $attributes = [];

    public function __construct($attributes = [])
    {
        $this->setAttributes($attributes);
    }

    public function __call($name, $args)
    {
        $method = substr((string) $name, 0, 3);
        $fieldName = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', substr((string) $name, 3)));
        switch ($method) {
            case 'get':
                return $this->attributes[$fieldName] ?? null;
            case 'set':
                $this->attributes[$fieldName] = $args[0];
                break;
        }
        return null;
    }

    public function setAttributes($attributes): void
    {
        $this->attributes = $attributes;
        foreach ($attributes as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}
