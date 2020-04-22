<?php

namespace App\EventListener;

use App\Exception\BaseException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Response\ErrorResponse;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $type = get_class($exception);

        $type = explode('\\', $type);
        $type = array_pop($type);

        $message = $exception->getMessage();
        if(!$message && $exception instanceof BaseException) {
            $message = $exception->getErrors();
        }

        $response = new ErrorResponse(
            $message,
            $exception->getCode(),
            $type
        );

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onException'
        );
    }
}
