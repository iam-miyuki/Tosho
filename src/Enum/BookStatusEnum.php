<?php

namespace App\Enum;

enum BookStatusEnum: string
{
    case available  = "Disponible";
    case borrowed = "Emprunté";
}
