<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Enums\Uf;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SimulacaoItemController extends Controller
{
    public function create(): View
    {
        return view('simulacoes.item.create', [
            'ufs'       => Uf::cases(),
            'simulacao' => null,
        ]);
    }
}
