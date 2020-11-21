<?php

namespace App\EventListener;

use App\Exception\JsonObjectValidationException;
use FOS\RestBundle\Exception\InvalidParameterException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

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

        switch (true) {
            case $exception instanceof JsonObjectValidationException:
                $response->setData([
                    'title'          => $exception->getMessage(),
                    'invalid-params' => array_map(function ($error) {
                        return [
                            'name'   => $error->getPropertyPath(),
                            'reason' => $error->getMessage()
                        ];
                    }, iterator_to_array($exception->getValidationErrors()))
                ]);

                break;
            case $exception instanceof InvalidParameterException:
                $response->setData([
                        'title'          => 'Invalid parameter',
                        'invalid-params' => [
                            'name'   => $exception->getParameter()->name,
                            'reason' => "Parameter missed or doesn't match requirements"
                        ]
                    ]
                );

                break;
            case $exception instanceof BadRequestHttpException:
                if ($exception->getPrevious() instanceof NotNormalizableValueException) {
                    $response->setData([
                        'title'  => "Invalid JSON parameter",
                        'detail' => "Failed to deserialize one of the JSON parameters correctly"
                    ]);

                    break;
                }
                // leave this space blank
            case $exception instanceof HttpExceptionInterface:
                $response->setData([
                        'title'  => 'Error',
                        'detail' => $exception->getMessage()
                    ]
                );

                break;
            default:
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                $response->setContent(json_encode(['title' => 'Internal server error', 'reason' => $exception->getMessage()]));
        }

        $event->setResponse($response);
    }
}