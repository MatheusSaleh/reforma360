<?php

declare(strict_types=1);

return [
    'explicacoes_cst' => [
        '000' => 'Aplica as alíquotas da operação sem redução, como numa venda tributada pela regra geral.',
        '010' => 'Aplica as alíquotas de um regime setorial, iguais em todo o país, como em operações de serviços financeiros.',
        '011' => 'Aplica alíquotas uniformes com redução. Nos planos de assistência à saúde, a redução é de 60%.',
        '200' => 'Reduz as alíquotas da operação. Nos serviços de educação do Anexo II, a redução é de 60%.',
        '220' => 'Aplica uma alíquota específica fixada em lei. Os três cClassTrib anteriores tiveram a vigência encerrada.',
        '221' => 'Aplica um percentual fixo sobre a base definida em lei, como a receita bruta em certas locações de imóveis.',
        '222' => 'Aplica a alíquota sobre uma base de cálculo menor, conforme a regra da operação.',
        '400' => 'Dispensa a cobrança do tributo por isenção prevista em lei.',
        '410' => 'A operação está protegida por imunidade constitucional ou fora das hipóteses de incidência do tributo.',
        '510' => 'Adia o recolhimento de todo ou parte do tributo para uma etapa ou evento posterior.',
        '515' => 'Combina a redução da alíquota com o adiamento de todo ou parte do recolhimento.',
        '550' => 'Suspende a cobrança enquanto forem cumpridas as condições do regime aplicável.',
        '620' => 'Concentra a cobrança em uma etapa da cadeia, como ocorre com determinados combustíveis.',
        '800' => 'Registra a transferência de créditos de IBS ou CBS nas hipóteses permitidas.',
        '810' => 'Registra o crédito presumido de IBS em fornecimentos elegíveis da Zona Franca de Manaus.',
        '811' => 'Registra ajustes na apuração do período, como débitos de notas fiscais ainda não processadas.',
        '820' => 'Identifica a operação cuja tributação é registrada em outro documento fiscal.',
        '830' => 'Exclui valores específicos da base, como a parcela elegível de energia elétrica compensada.',
    ],
];
