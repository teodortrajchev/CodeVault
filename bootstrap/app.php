<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\SendDueRemindersDayBefore::class,
    ]);
    })
        ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 403 || $request->expectsJson()) {
                return null;
            }

            // Go back where the user came from, but never loop back to the forbidden page itself.
            $previous = url()->previous(route('projects.index'));
            $target = $previous === $request->fullUrl() ? route('projects.index') : $previous;

            $message = $e->getMessage();
            $custom = ($message !== '' && $message !== 'This action is unauthorized.') ? $message : true;

            return redirect($target)->with('forbidden', $custom);
        });
    })->create();

