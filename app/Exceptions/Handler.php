<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if (! ($e instanceof ApiException)) {
            return parent::render($request, $e);
        }

        $res = [
            'error' => $e->errorType,
            'error_description' => $e->getMessage()
        ];

        if ($code = $e->getCode()) {
            $res['error_code'] = $code;
        }
        if ($uri = $e->errorUri) {
            $res['error_uri'] = $uri;
        }

        return new JsonResponse($res, $e->httpStatusCode, $e->getHttpHeaders());
    }
}
