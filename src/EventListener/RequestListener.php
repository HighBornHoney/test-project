<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class RequestListener
{
    public function __construct(
        private SerializerInterface $serializer,
    )
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (isset($_ENV['AUTH_TOKEN']) && $_ENV['AUTH_TOKEN'] !== '') {
            $token = $request->query->get('token');
            if ($token !== $_ENV['AUTH_TOKEN']) {
                $response = new Response($this->serializer->serialize(['error' => 'Unauthorized'], $_ENV['FORMAT']), 401);
                $event->setResponse($response);
            }
        }
    }
}
