<?php

namespace App\Enum;

enum InventoryStatusEnum: string
{
    case ok  = "Validé";
    case location = "Mal rangé";
    case label = "L'étiquette décollée / déchirée";
    case other = "Autre";
}