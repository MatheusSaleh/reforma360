<?php

declare(strict_types=1);

namespace App\Importers;

/**
 * Colunas do arquivo de catálogo e os nomes aceitos no cabeçalho (já em "slug":
 * minúsculas, sem acento, espaços viram "_").
 */
final class ColunasCatalogo
{
    /**
     * @var array<string, list<string>>
     */
    private const array ALIASES = [
        'descricao'    => ['descricao', 'descricao_do_produto', 'produto', 'nome'],
        'ncm'          => ['ncm', 'codigo_ncm'],
        'nbs'          => ['nbs', 'codigo_nbs'],
        'cst'          => ['cst', 'cst_ibs_cbs', 'cst_ibscbs'],
        'c_class_trib' => ['c_class_trib', 'cclasstrib', 'classificacao_tributaria', 'class_trib'],
        'preco'        => ['preco', 'preco_r', 'valor', 'valor_r', 'preco_unitario'],
    ];

    /**
     * Colunas obrigatórias no cabeçalho (NCM e NBS: pelo menos uma das duas).
     */
    private const array OBRIGATORIAS = ['descricao', 'cst', 'c_class_trib', 'preco'];

    /**
     * Ordem e títulos da planilha modelo.
     *
     * @var array<string, string>
     */
    public const array TITULOS = [
        'descricao'    => 'descricao',
        'ncm'          => 'ncm',
        'nbs'          => 'nbs',
        'cst'          => 'cst',
        'c_class_trib' => 'c_class_trib',
        'preco'        => 'preco',
    ];

    public static function canonica(string $cabecalho): ?string
    {
        foreach (self::ALIASES as $coluna => $aliases) {
            if (in_array($cabecalho, $aliases, true)) {
                return $coluna;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $presentes  colunas canônicas encontradas
     * @return list<string>
     */
    public static function faltando(array $presentes): array
    {
        $faltando = array_values(array_diff(self::OBRIGATORIAS, $presentes));

        if (! in_array('ncm', $presentes, true) && ! in_array('nbs', $presentes, true)) {
            $faltando[] = 'ncm ou nbs';
        }

        return $faltando;
    }
}
