<?php

declare(strict_types=1);

namespace App\Presentation\Transformer;

abstract class AbstractTransformer implements TransformerInterface
{
    public function transformCollection(array $collection): array
    {
        return array_map($this->transform(...), $collection);
    }
}
