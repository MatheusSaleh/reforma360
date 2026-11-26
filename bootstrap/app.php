<?php

declare(strict_types=1);

use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (CalculadoraIndisponivelException $e, Request $request) {
            $mensagem = 'A calculadora da Receita está indisponível no momento. Tente novamente em instantes.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $mensagem], 503);
            }

            return back()->withInput()->withErrors(['simulacao' => $mensagem]);
        });
    })->create();
