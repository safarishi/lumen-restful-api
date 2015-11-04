<?php

namespace App\Exceptions;

class DuplicateOperationException extends ApiException
{
    public function __construct($msg)
    {
        parent::__construct($msg, 14005);
        $this->httpStatusCode = 400;
        $this->errorType = 'invalid_operate';
    }
}