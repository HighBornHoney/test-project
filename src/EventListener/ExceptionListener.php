<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListener
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        if ($exception instanceof NotFoundHttpException) {
            $response = new Response($this->serializer->serialize(['error' => 'Resource not found'], $_ENV['FORMAT']), 404);
        } elseif ($exception instanceof NotEncodableValueException || $exception instanceof NotNormalizableValueException) {
            $response = new Response($this->serializer->serialize(['error' => 'Bad request'], $_ENV['FORMAT']), 400);
        }
        
        if (isset($response)) {
            $event->setResponse($response);
        }
    }
}
