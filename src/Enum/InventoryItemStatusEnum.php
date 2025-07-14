<?php

namespace App\Enum;

enum InventoryItemStatusEnum: string
{
    case ok  = "Validé";
    case badLocation = "Mal rangé";
    case withoutCode = "Code manquant";
    case notFound = "Livre non trouvé";
    case other = "Autre";
}