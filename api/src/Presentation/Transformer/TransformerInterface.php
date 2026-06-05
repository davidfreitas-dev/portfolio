<?php

declare(strict_types=1);

namespace App\Presentation\Transformer;

interface TransformerInterface
{
    /**
     * Transform a single entity or object into an array.
     */
    public function transform(object $object): array;

    /**
     * Transform a collection of entities or objects.
     */
    public function transformCollection(array $collection): array;
}
