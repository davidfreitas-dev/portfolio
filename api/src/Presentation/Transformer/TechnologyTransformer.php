<?php

declare(strict_types=1);

namespace App\Presentation\Transformer;

use App\Domain\Entity\Technology;

class TechnologyTransformer extends AbstractTransformer
{
    public function __construct(private readonly string $apiUrl)
    {
    }

    /**
     * @param Technology $object
     */
    public function transform(object $object): array
    {
        return [
            'id' => $object->id,
            'name' => $object->name,
            'slug' => $object->slug,
            'image' => $object->image,
            'image_url' => $object->image ? sprintf('%s/images/technologies/%s', $this->apiUrl, $object->image) : null,
            'sort_order' => $object->sortOrder,
            'created_at' => $object->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $object->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
