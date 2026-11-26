<?php

declare(strict_types=1);

namespace App\Providers;

use App\Importers\CsvImporter;
use App\Importers\NfeXmlImporter;
use App\Importers\XlsxImporter;
use App\Integrations\Calculadora\Contracts\CalculadoraTributos;
use App\Integrations\Calculadora\Contracts\DadosAbertos;
use App\Integrations\Calculadora\Contracts\GeradorDanfse;
use App\Integrations\Calculadora\Contracts\XmlTributacao;
use App\Integrations\Calculadora\DadosAbertosClient;
use App\Integrations\Calculadora\DanfseClient;
use App\Integrations\Calculadora\ReceitaCalculadoraClient;
use App\Integrations\Calculadora\XmlTributacaoClient;
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
        $this->app->bind(XmlTributacao::class, XmlTributacaoClient::class);
        $this->app->bind(GeradorDanfse::class, DanfseClient::class);
        $this->app->tag([CsvImporter::class, XlsxImporter::class, NfeXmlImporter::class], 'catalog-importers');
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
