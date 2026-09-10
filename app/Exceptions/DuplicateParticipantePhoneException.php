<?php

namespace App\Exceptions;

use App\Models\Participante;
use RuntimeException;

class DuplicateParticipantePhoneException extends RuntimeException
{
    public function __construct(public readonly Participante $participante)
    {
        parent::__construct('Duplicate participante phone.');
    }
}
