<?php

declare(strict_types=1);

namespace App\Http\Controllers\Simulacao;

use App\Enums\Uf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Simulacao\SimularItemRequest;
use App\Services\Simulacao\SimularItemService;
use Illuminate\Contracts\View\View;

class SimularItemController extends Controller
{
    public function __invoke(SimularItemRequest $request, SimularItemService $service): View
    {
        return view('simulacoes.item.create', [
            'ufs'       => Uf::cases(),
            'simulacao' => $service->simular($request->dados()),
        ]);
    }
}
