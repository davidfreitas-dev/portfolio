<?php

declare(strict_types=1);

namespace App\Presentation\Transformer;

use App\Domain\Entity\Experience;

class ExperienceTransformer extends AbstractTransformer
{
    /**
     * @param Experience $object
     */
    public function transform(object $object): array
    {
        return [
            'id' => $object->id,
            'title' => $object->title,
            'description' => $object->description,
            'start_date' => $object->startDate->format('Y-m-d'),
            'end_date' => $object->endDate?->format('Y-m-d'),
            'sort_order' => $object->sortOrder,
            'created_at' => $object->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $object->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
