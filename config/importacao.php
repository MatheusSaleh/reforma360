<?php

declare(strict_types=1);

return [

    /*
    | Tamanho máximo do arquivo enviado, em KB.
    */

    'tamanho_maximo_kb' => (int) env('IMPORTACAO_TAMANHO_MAXIMO_KB', 5120),

    /*
    | Linhas de produtos por arquivo e linhas processadas por lote.
    */

    'maximo_linhas' => (int) env('IMPORTACAO_MAXIMO_LINHAS', 5000),

    'tamanho_lote' => 500,

    /*
    | Fila do job de processamento.
    */

    'fila' => 'importacoes',

    /*
    | Dias que o XML de uma NFS-e importada fica guardado (disco privado) para gerar
    | o DANFSe. Depois disso o comando importacoes:apagar-arquivos-vencidos apaga.
    */

    'retencao_xml_dias' => (int) env('IMPORTACAO_RETENCAO_XML_DIAS', 30),

];
