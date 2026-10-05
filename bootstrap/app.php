<?php

use Illuminate\Auth\Access\AuthorizationException;
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
