<?php

namespace App\EventListener;

use App\Service\ActivityLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLogger $activityLogger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        $request = $event->getRequest();

        if ($user) {
            $this->activityLogger->log(
                $user,
                'LOGOUT',
                'User',
                (string)$user->getId(),
                ['email' => $user->getUserIdentifier()]
            );
        }
    }
}

