<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
| XMLs de NFS-e guardados para o DANFSe: apagados depois da retenção.
*/
Schedule::command('importacoes:apagar-arquivos-vencidos')->dailyAt('03:00')->withoutOverlapping();
