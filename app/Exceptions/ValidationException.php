<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends ApiException
{
    public function __construct($msg)
    {
        parent::__construct($msg, 14001);
        $this->httpStatusCode = 400;
        $this->errorType = 'invalid_request';
    }
}