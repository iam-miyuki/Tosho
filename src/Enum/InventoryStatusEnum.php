<?php

namespace App\Enum;

enum InventoryStatusEnum: string
{
    case open  = "Session ouverte";
    case closed = "Session fermé";
    case finished = "Session terminé";
    case upcoming = "Session à venir";
}
