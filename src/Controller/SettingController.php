<?php

namespace App\Controller;

use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SettingController extends AbstractController
{
    public function __construct(
        private LocaleSwitcher $localeSwitcher,
        private RequestStack $request
    ) {
    }

    // #[Route(path:'/switch-locale', name: 'switch_locale')]
    // public function switchLocale(Request $request, SessionInterface $session)
    // {
    //     $this->localeSwitcher->setLocale('en');
    //     $session->set('_locale', $request->getSession()->get('_locale_choice'));
    //     return $this->redirectToRoute('home');
    // }
}
