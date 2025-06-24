<?php
namespace App\Enum;

enum LoanStatusEnum: string
{
    case inProgress = "En cours";
    case returned = "Rendu";
    case delayed = "En retard";
}