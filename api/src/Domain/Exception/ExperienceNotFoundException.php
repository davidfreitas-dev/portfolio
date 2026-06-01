<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class ExperienceNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Experiência com ID $id não encontrada.", 404);
    }
}
