<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;


class SendDueRemindersDayBefore
{
    private const SEND_HOUR = 9;

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /** Runs after the response has been sent, so the visitor isn't kept waiting. */
    public function terminate(Request $request, Response $response): void
    {
        if (now()->hour < self::SEND_HOUR) {
            return;
        }

        $key = 'due-reminders-sent:' . today()->toDateString();

        // Cache::add is atomic: only one request per day gets past this line.
        // if (! Cache::add($key, true, now()->endOfDay())) {
        //     return;
        // }

        try {
            Artisan::call('reminders:send-due');
        } catch (Throwable $e) {
            Cache::forget($key); // let the next request retry
            Log::error('Automatic due reminders failed', ['error' => $e->getMessage()]);
        }
    }
}