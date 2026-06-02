<?php

declare(strict_types=1);

namespace App\Application\DTO\Project;

use App\Domain\Exception\ProjectValidationException;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ProjectRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: "O título é obrigatório.")]
        public string $title,
        #[Assert\NotBlank(message: "A descrição é obrigatória.")]
        public string $description,
        public ?string $slug = null,
        public ?string $summary = null,
        public ?string $link = null,
        public ?string $github_link = null,
        public int $sort_order = 0,
        public bool $is_active = true,
        public ?UploadedFileInterface $image = null,
    ) {
    }

    public function validate(ValidatorInterface $validator): void
    {
        $violations = $validator->validate($this);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new ProjectValidationException($errors);
        }
    }
}
