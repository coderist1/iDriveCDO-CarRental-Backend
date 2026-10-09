<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );

        // Turn rule violations raised by PostgreSQL (triggers, CHECK, UNIQUE, EXCLUDE,
        // FOREIGN KEY) into client errors instead of 500s.
        $exceptions->render(function (QueryException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $detail = (string) ($e->getPrevious()?->errorInfo[2] ?? '');
            preg_match('/ERROR:\s+(.+?)(?:\R|$)/', $detail, $match);
            preg_match('/constraint "([^"]+)"/', $detail, $constraint);

            [$status, $message] = match ((string) $e->getCode()) {
                'P0001' => [422, $match[1] ?? 'The request was rejected by a database rule.'],
                '23P01' => [409, 'This vehicle is already booked for some of those dates.'],
                '23505' => [409, 'A record with the same unique value already exists.'],
                '23503' => [409, 'This record is linked to other records and cannot be changed or deleted.'],
                '23514' => [422, match ($constraint[1] ?? null) {
                    'ck_bookings_confirmed_paid' => 'A booking must be paid before it can be confirmed, started or completed.',
                    'ck_bookings_paid_method' => 'A paid booking needs a payment method.',
                    'ck_bookings_chauffeur' => 'Chauffeur bookings need a driver; self-drive bookings must not have one.',
                    'ck_bookings_days' => 'A booking must last between 1 and 30 days.',
                    'ck_payments_brand' => 'Cash payments use the brand "Cash"; cashless payments must be GCash, Maya or GrabPay.',
                    'ck_renter_docs_kind' => 'Provide either an ID (type and number) or the full driver\'s licence details.',
                    default => 'The data breaks a database rule.',
                }],
                default => [null, null],
            };

            if ($status === null) {
                return null;
            }

            return response()->json(array_filter([
                'message' => $message,
                'constraint' => $constraint[1] ?? null,
            ]), $status);
        });

        // A tampered or expired verification link should explain itself and offer
        // a resend, rather than showing a bare 403 page.
        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if (! $request->routeIs('verification.verify') || $request->expectsJson()) {
                return null;
            }

            $expired = (int) $request->query('expires', 0) < now()->getTimestamp();

            return redirect()->route('verification.notice')->with(
                'status', $expired ? 'verification-link-expired' : 'verification-link-invalid'
            );
        });

        // Raised by EmailVerificationRequest when the id/hash pair does not match
        // the authenticated user.
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->routeIs('verification.verify') || $request->expectsJson()) {
                return null;
            }

            return redirect()->route('verification.notice')
                ->with('status', 'verification-link-invalid');
        });
    })->create();
