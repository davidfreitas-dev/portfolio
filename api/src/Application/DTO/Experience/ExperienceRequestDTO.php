<?php

declare(strict_types=1);

namespace App\Application\DTO\Experience;

use App\Domain\Exception\ExperienceValidationException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ExperienceRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: "O título é obrigatório.")]
        #[Assert\Length(max: 128, maxMessage: "O título não pode ter mais de 128 caracteres.")]
        public string $title,
        #[Assert\NotBlank(message: "A descrição é obrigatória.")]
        public string $description,
        #[Assert\NotBlank(message: "A data de início é obrigatória.")]
        #[Assert\Date(message: "A data de início deve estar no formato YYYY-MM-DD.")]
        public string $start_date,
        #[Assert\Date(message: "A data de término deve estar no formato YYYY-MM-DD.")]
        public ?string $end_date = null,
        public int $sort_order = 0,
    ) {
    }

    public function validate(ValidatorInterface $validator): void
    {
        $violations = $validator->validate($this);

        $errors = [];
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }

        // Custom validation for date interval
        if ($this->start_date !== '' && $this->start_date !== '0' && (!in_array($this->end_date, [null, '', '0'], true))) {
            $start = \DateTimeImmutable::createFromFormat('Y-m-d', $this->start_date);
            $end = \DateTimeImmutable::createFromFormat('Y-m-d', $this->end_date);

            if ($start && $end && $start > $end) {
                $errors['end_date'] = "A data de início não pode ser maior que a data final.";
            }
        }

        if ($errors !== []) {
            throw new ExperienceValidationException($errors);
        }
    }
}
