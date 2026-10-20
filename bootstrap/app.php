<?php

use App\Exceptions\EntityHasDependenciesException;
use App\Exceptions\InsufficientStockException;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckSessionValidity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan alias 'role' untuk CheckRole middleware
        $middleware->alias([
            'role'            => CheckRole::class,
            'session.valid'   => CheckSessionValidity::class,
        ]);

        // Tambahkan CheckSessionValidity ke grup web (setelah auth) untuk semua halaman
        $middleware->appendToGroup('web', CheckSessionValidity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tangani InsufficientStockException — HTTP 422
        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        });

        // Tangani EntityHasDependenciesException — HTTP 422
        $exceptions->render(function (EntityHasDependenciesException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        });
    })->create();
