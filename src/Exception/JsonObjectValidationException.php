<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class JsonObjectValidationException extends \Symfony\Component\HttpKernel\Exception\HttpException
{
    /**
     * @var ConstraintViolationListInterface
     */
    private ConstraintViolationListInterface $validationErrors;

    /**
     * JsonObjectValidationException constructor.
     * @param ConstraintViolationListInterface $validationErrors
     * @param string                           $message
     */
    public function __construct(ConstraintViolationListInterface $validationErrors, string $message = '')
    {
        $this->validationErrors = $validationErrors;

        if (empty($message)) {
            $message = "Json validation error";
        }

        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }
}