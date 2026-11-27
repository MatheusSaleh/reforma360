<?php

declare(strict_types=1);

use App\Integrations\Calculadora\Exceptions\CalculadoraIndisponivelException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability'   => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (CalculadoraIndisponivelException $e, Request $request) {
            $mensagem = $e->mensagemParaUsuario();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $mensagem], 503);
            }

            return back()->withInput()->withErrors(['simulacao' => $mensagem]);
        });

        // Mensagens da API pública em português (as do Laravel/Sanctum não passam por tradução).
        $exceptions->render(fn (AuthenticationException $e, Request $request) => $request->is('api/*')
            ? response()->json(['message' => 'Não autenticado. Envie um token válido no cabeçalho Authorization.'], 401)
            : null);

        $exceptions->render(fn (AccessDeniedHttpException $e, Request $request) => $request->is('api/*') && $e->getPrevious() instanceof MissingAbilityException
            ? response()->json(['message' => 'O token não tem a permissão necessária para esta operação.'], 403)
            : null);

        $exceptions->render(fn (ThrottleRequestsException $e, Request $request) => $request->is('api/*')
            ? response()->json(['message' => 'Limite de requisições atingido. Tente de novo em instantes.'], 429, $e->getHeaders())
            : null);
    })->create();
