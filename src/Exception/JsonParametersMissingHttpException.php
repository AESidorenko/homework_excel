<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JsonParametersMissingHttpException extends HttpException
{
    public function __construct(array $parameters, string $message = '')
    {
        $message = json_encode([
            'title'          => empty($message) ? 'Json request parameters missing' : $message,
            'invalid-params' => array_map(fn($name) => ['name' => $name, 'reason' => 'parameter missing'], $parameters)
        ]);

        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }
}