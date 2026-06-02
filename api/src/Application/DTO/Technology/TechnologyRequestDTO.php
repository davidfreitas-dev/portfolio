<?php

declare(strict_types=1);

namespace App\Application\DTO\Technology;

use App\Domain\Exception\TechnologyValidationException;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class TechnologyRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: "O nome é obrigatório.")]
        #[Assert\Length(max: 64, maxMessage: "O nome não pode ter mais de 64 caracteres.")]
        public string $name,
        public ?string $slug = null,
        public int $sort_order = 0,
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
            throw new TechnologyValidationException($errors);
        }
    }
}
