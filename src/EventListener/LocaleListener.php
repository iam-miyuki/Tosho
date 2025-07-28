<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class LocaleListener
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
    }

    #[AsEventListener]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // Essaie de prendre la locale dans la session
        $locale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);
    }
}
