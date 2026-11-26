/**
 * Progresso da simulação em lote em tempo real (Reverb). Se o WebSocket não
 * conectar, recarrega a página periodicamente enquanto a simulação estiver em andamento.
 */

const RECARGA_SEM_WEBSOCKET_MS = 5000;

export function iniciarProgressoSimulacao() {
    const painel = document.querySelector('[data-progresso-simulacao]');

    if (!painel) {
        return;
    }

    const barra = painel.querySelector('[data-progresso-barra]');
    const texto = painel.querySelector('[data-progresso-texto]');
    const erros = painel.querySelector('[data-progresso-erros]');
    const progresso = painel.querySelector('[role="progressbar"]');
    const status = document.querySelector('[data-status-simulacao]');

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

    window.Echo.private(painel.dataset.canal)
        .error(recarregarPeriodicamente)
        .listen('.simulacao.atualizada', (dados) => {
            barra.style.width = `${dados.percentual}%`;
            progresso.setAttribute('aria-valuenow', String(dados.percentual));
            texto.textContent = `${dados.processados} de ${dados.total}`;
            erros.textContent = dados.com_erro > 0 ? `${dados.com_erro} com erro até agora.` : '';

            if (status) {
                status.textContent = dados.status_label;
            }

            if (!dados.em_andamento) {
                window.location.reload();
            }
        });
}
