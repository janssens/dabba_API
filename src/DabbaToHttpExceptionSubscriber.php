<?php

namespace App;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\Util\ErrorFormatGuesser;
use App\Exception\DabbaException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

final class DabbaToHttpExceptionSubscriber implements EventSubscriberInterface
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): iterable
    {
        return [ KernelEvents::EXCEPTION => 'convertException'];
    }

    public function convertException(ExceptionEvent $event): void
    {
        $exception = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException(); // @phpstan-ignore-line
        //$exceptionClass = \get_class($exception);

        if ($exception instanceof DabbaException) {
            $event->setResponse(new Response(
                $this->serializer->serialize( ['message'=>$exception->getMessage(),'code'=>$exception->getCode()], 'json'),
                $exception->getCode(),
                [
                    'Content-Type' => sprintf('%s; charset=utf-8', 'application/json'),
                    'X-Content-Type-Options' => 'nosniff',
                    'X-Frame-Options' => 'deny',
                ]
            ));
        }
    }
}