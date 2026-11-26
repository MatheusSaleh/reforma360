<?php

declare(strict_types=1);

namespace App\Providers;

use App\Integrations\Calculadora\Contracts\CalculadoraTributos;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\DadosAbertosClient;
use App\Integrations\Calculadora\ReceitaCalculadoraClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CalculadoraTributos::class, ReceitaCalculadoraClient::class);
        $this->app->bind(DadosAbertos::class, DadosAbertosClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('simulacoes', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('dados-abertos', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));
    }
}
