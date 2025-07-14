<?php

namespace App\Enum;

enum InventoryStatusEnum: string
{
    case open  = "Session ouverte";
    case finished = "Session finished";
    case upcoming = "Session à venir";
}
