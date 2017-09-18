<?php

namespace App\Exceptions;

use iHubGrid\ErrorHandler\Exceptions\Api\ITypicalError;
use iHubGrid\ErrorHandler\Exceptions\Api\Traits\ApiHandlerTrait;
use iHubGrid\ErrorHandler\Facades\AppLog;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\Debug\Exception\FlattenException;

class Handler extends ExceptionHandler
{
    use ApiHandlerTrait;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];


    /**
     * @param Exception $exception
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }
        
        $this->reportWithLogger($exception);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function capture($request)
    {
        $route = $request->route();

        if (!$route)
        {
            return false;
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $this->capture($request);

        if ($controller = $this->isApiCall($request)) {
            $response = $this->getApiExceptionResponse($controller, $exception);
            if ($response instanceof Response) {
                $logGroup = '';
                // write typical errors to group log index
                if ($exception instanceof ITypicalError && $exception->isTypicalError()) {
                    $logGroup = 'response-warning';
                }
                AppLog::error([
                    'request' => $request->getContent(),
                    'response' => $response->getContent()
                ], $this->getNodeName(), 'response-error', '', $logGroup);

                return $response;
            }
        }

        if ($this->isHttpException($exception)) {
            return $this->renderHttpException($exception);
        }

        if (config('app.debug')) {
            return $this->renderExceptionWithWhoops($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Create a Symfony response for the given exception.
     *
     * @param  \Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertExceptionToResponse(Exception $e)
    {
        $e = FlattenException::create($e);

        return \Illuminate\Support\Facades\Response::make(config('app.debug') ? $e->getMessage() : '', $e->getStatusCode(), $e->getHeaders());
    }

    /**
     * Render an exception using Whoops.
     *
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    protected function renderExceptionWithWhoops(Exception $e)
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());

        return new \Illuminate\Http\Response(
            $whoops->handleException($e),
            method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500,
            method_exists($e, 'getHeaders') ? $e->getHeaders() : []
        );
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
