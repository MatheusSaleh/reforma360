/**
 * Progresso em tempo real (Reverb) de uma simulação (data-canal) ou de várias
 * somadas, como os 8 anos de uma linha do tempo (data-canais, JSON). Se o WebSocket
 * não conectar, recarrega a página periodicamente enquanto houver algo em andamento.
 */

const RECARGA_SEM_WEBSOCKET_MS = 5000;

export function iniciarProgressoSimulacao() {
    const painel = document.querySelector('[data-progresso-simulacao]');

    if (!painel) {
        return;
    }

    const canais = painel.dataset.canais ? JSON.parse(painel.dataset.canais) : [painel.dataset.canal];
    const barra = painel.querySelector('[data-progresso-barra]');
    const texto = painel.querySelector('[data-progresso-texto]');
    const erros = painel.querySelector('[data-progresso-erros]');
    const progresso = painel.querySelector('[role="progressbar"]');
    const status = canais.length === 1 ? document.querySelector('[data-status-simulacao]') : null;
    const porCanal = new Map();
    const totalInicial = Number(painel.dataset.total || 0);
    const processadosIniciais = Number(painel.dataset.processados || 0);

    let recarga = null;
    const recarregarPeriodicamente = () => {
        recarga ??= setInterval(() => window.location.reload(), RECARGA_SEM_WEBSOCKET_MS);
    };

    if (!window.Echo) {
        recarregarPeriodicamente();
        return;
    }

    const conexao = window.Echo.connector?.pusher?.connection;
    conexao?.bind('unavailable', recarregarPeriodicamente);
    conexao?.bind('failed', recarregarPeriodicamente);

    const atualizar = () => {
        const valores = [...porCanal.values()];
        const todosResponderam = porCanal.size === canais.length;
        // Enquanto nem todos os canais responderam, usa o total do servidor e soma o que já chegou.
        const total = todosResponderam ? valores.reduce((soma, d) => soma + d.total, 0) : totalInicial;
        const processados = Math.max(processadosIniciais, valores.reduce((soma, d) => soma + d.processados, 0));
        const comErro = valores.reduce((soma, d) => soma + d.com_erro, 0);
        const percentual = total > 0 ? Math.floor((processados * 100) / total) : 0;

        barra.style.width = `${percentual}%`;
        progresso.setAttribute('aria-valuenow', String(percentual));

        texto.textContent = `${processados} de ${total}`;

        erros.textContent = comErro > 0 ? `${comErro} com erro até agora.` : '';

        const terminou = todosResponderam && valores.every((d) => !d.em_andamento);

        if (terminou) {
            window.location.reload();
        }
    };

    canais.forEach((canal) => {
        window.Echo.private(canal)
            .error(recarregarPeriodicamente)
            .listen('.simulacao.atualizada', (dados) => {
                porCanal.set(canal, dados);

                if (status) {
                    status.textContent = dados.status_label;
                }

                atualizar();
            });
    });
}
