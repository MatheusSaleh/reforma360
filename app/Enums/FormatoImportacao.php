<?php

declare(strict_types=1);

namespace App\Enums;

enum FormatoImportacao: string
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';

    public static function daExtensao(string $extensao): ?self
    {
        return match (mb_strtolower($extensao)) {
            'csv', 'txt' => self::Csv,
            'xlsx'       => self::Xlsx,
            default      => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Csv  => 'CSV',
            self::Xlsx => 'Excel (XLSX)',
        };
    }
}
