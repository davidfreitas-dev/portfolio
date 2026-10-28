<?php

declare(strict_types=1);

namespace App\Presentation\Transformer;

use App\Domain\Entity\Project;

class ProjectTransformer extends AbstractTransformer
{
    public function __construct(private readonly string $apiUrl)
    {
    }

    /**
     * @param Project $object
     */
    public function transform(object $object): array
    {
        return [
            'id' => $object->id,
            'title' => $object->title,
            'slug' => $object->slug,
            'summary' => $object->summary,
            'description' => $object->description,
            'image' => $object->image,
            'image_url' => $object->image ? sprintf('%s/images/projects/%s', $this->apiUrl, $object->image) : null,
            'links' => [
                'demo' => $object->link,
                'github' => $object->githubLink,
            ],
            'sort_order' => $object->sortOrder,
            'is_active' => $object->isActive,
            'technologies' => array_values($object->technologies),
            'created_at' => $object->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $object->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
