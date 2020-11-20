<?php

namespace App\EventListener;

use FOS\RestBundle\Exception\InvalidParameterException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();

        $response = new JsonResponse(
            null,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [
                'Content-Type' => 'application/problem+json',
            ]
        );

        if ($exception instanceof InvalidParameterException) {
            $response->setData([
                'title'          => 'Invalid request parameter',
                'invalid-params' => [
                    'name'   => $exception->getParameter()->getName(),
                    'reason' => 'parameter missed or has invalid format'
                ]
            ]);
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->add($exception->getHeaders());
            $exceptionMessageData = json_decode($exception->getMessage(), true);
            if ($exceptionMessageData === null) {
                $response->setData([
                        'title'  => 'Error',
                        'detail' => $exception->getMessage()
                    ]
                );
            } else {
                $response->setData($exceptionMessageData);
            }
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setContent(json_encode([
                'title' => 'Internal server error',
            ]));
        }

        $event->setResponse($response);
    }
}