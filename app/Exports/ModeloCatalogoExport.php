<?php

declare(strict_types=1);

namespace App\Exports;

use App\Importers\ColunasCatalogo;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Planilha modelo para importação: cabeçalho e exemplos (dados fictícios dos exemplos
 * oficiais da calculadora). Colunas de código em formato texto para o Excel não
 * apagar zeros à esquerda.
 */
final class ModeloCatalogoExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithCustomCsvSettings, WithHeadings
{
    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        return [
            // Colunas is_*: Imposto Seletivo (a partir de 2027). A alíquota fica em branco de propósito:
            // ainda não está em lei e cada empresa informa a sua estimativa.
            ['Arroz tipo 1 (cesta básica)', '10063021', '', '200', '200003', '28,90', '', '', '', '', '', ''],
            ['Cerveja lata 350 ml', '22030000', '', '000', '000001', '4,50', '', '', 'LT', '0,35', '000', '000001'],
            ['Notebook 14 polegadas', '84713012', '', '000', '000001', '3899,00', '', '', '', '', '', ''],
            ['Curso de idiomas (mensalidade)', '', '122011200', '200', '200028', '450,00', '', '', '', '', '', ''],
        ];
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return array_values(ColunasCatalogo::TITULOS);
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
     * CSV no padrão do Excel em pt-BR: ";" e BOM para abrir com acentos corretos.
     *
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array
    {
        return ['delimiter' => ';', 'use_bom' => true];
    }
}
