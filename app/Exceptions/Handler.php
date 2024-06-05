<?php

namespace App\Exceptions;

use App\Http\Kernel;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\ViewErrorBag;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ApplicationDomainException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): mixed
    {
        // TODO:: old version of 404
        //custom 404 page
        /*if ($this->isHttpException($e)) {
            if (request()->is('admin/*') || request()->is('static-admin/*')) {
                if ($e->getStatusCode() == 404) {
                    return response()->view('default.admin.404', ['errors' => new ViewErrorBag()], 404);
                }
            }
            else
            {
                if ($e->getStatusCode() == 404) {
                    return response()->view('default.404',  ['errors' => new ViewErrorBag()], 404);
                }
            }

        }

        if ($e instanceof ApplicationDomainException) {
            return response()->view('default.application-domain-exception', [
                'message' => $e->getMessage()
            ]);
        }

        return parent::render($request, $e);*/


        //custom 404 page
        // convert ModelNotFoundException into NotFoundHttpException
        if ($e instanceof ModelNotFoundException) {
//            dd('1122 2');
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof NotFoundHttpException) {
//            dd('1122 3');
            return response()->view('default.404', [], 404);
        }

        return parent::render($request, $e);
    }
}
