<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use Exception;

class ProjectNotFoundException extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Projeto com ID $id não encontrado.", 404);
    }
}
