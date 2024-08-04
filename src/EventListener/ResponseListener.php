<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseListener
{
    public function __construct()
    {
        
    }
    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        
        if ($_ENV['FORMAT'] === 'json') {
            $response->headers->replace(['Content-type' => 'application/json']);
        } elseif($_ENV['FORMAT'] === 'xml') {
            $response->headers->replace(['Content-type' => 'application/xml']);
        }
        
    }
}
