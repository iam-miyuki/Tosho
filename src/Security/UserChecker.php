<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Vérifie que seuls les bibliothécaires actifs peuvent se connecter
        if (in_array('ROLE_LIBRARIEN', $user->getRoles(), true) && !$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte bibliothécaire est désactivé. Veuillez contacter un administrateur.'
            );
        }
    }
}
