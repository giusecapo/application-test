<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class StatefulFirewallEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            LogoutEvent::class => ['setResponse', 0]
        );
    }

    /**
     * Customize the logout response to avoid the custom symfony redirect response.
     */
    public function setResponse(LogoutEvent $logoutEvent): void
    {
        $response = new JsonResponse(['success' => true], JsonResponse::HTTP_OK);
        $logoutEvent->setResponse($response);
    }
}
