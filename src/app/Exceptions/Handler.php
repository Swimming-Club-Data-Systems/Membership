<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Throwable;
use Inertia\Inertia;

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
        //
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prepare exception for rendering.
     *
     * @param  \Throwable  $e
     *
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        $debug = app()->environment(['local', 'testing', 'debug']);

        if (!$debug && $e instanceof TenantCouldNotBeIdentifiedOnDomainException) {
            return redirect(route('central.home'));
        }

        if (!$debug && in_array($response->status(), [500, 503, 404, 403])) {
            return Inertia::render('Errors/Error', ['status' => $response->status(), 'message' => $response->statusText()])
                ->toResponse($request)
                ->setStatusCode($response->status());
        } else if ($response->status() === 419) {
            return back()->with([
                'error' => 'The page expired, please try again.',
            ]);
        }

        return $response;
    }
}
