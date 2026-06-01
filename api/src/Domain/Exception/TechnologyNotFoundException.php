<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class TechnologyNotFoundException extends Exception
{
    public function __construct(int|string $id)
    {
        $identifier = is_int($id) ? "ID $id" : "slug '$id'";
        parent::__construct("Tecnologia com $identifier não encontrada.", 404);
    }
}
