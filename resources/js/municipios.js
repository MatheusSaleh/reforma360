/**
 * Carrega os municípios da UF selecionada no formulário de simulação.
 */
export function iniciarSeletorDeMunicipio() {
    const uf = document.querySelector('[data-uf]');
    const municipio = document.querySelector('[data-municipio]');

    if (!uf || !municipio) {
        return;
    }

    const ordem = new Intl.Collator('pt-BR');
    let requisicao;

    const opcao = (valor, texto) => {
        const elemento = document.createElement('option');
        elemento.value = valor;
        elemento.textContent = texto;
        return elemento;
    };

    async function carregar() {
        requisicao?.abort();
        const estado = uf.value;
        municipio.replaceChildren(opcao('', estado ? 'Carregando...' : 'Selecione a UF primeiro'));

        if (!estado) {
            return;
        }

        requisicao = new AbortController();
        const { signal } = requisicao;

        try {
            const resposta = await fetch(municipio.dataset.url.replace('__UF__', estado), {
                headers: { Accept: 'application/json' },
                signal,
            });

            if (!resposta.ok) {
                throw new Error(String(resposta.status));
            }

            const lista = await resposta.json();
            if (signal.aborted) {
                return;
            }

            lista.sort((a, b) => ordem.compare(a.nome, b.nome));
            municipio.replaceChildren(
                opcao('', lista.length ? 'Selecione' : 'Nenhum município encontrado'),
                ...lista.map((m) => opcao(m.codigo, m.nome)),
            );
            municipio.value = municipio.dataset.selecionado || '';
            if (municipio.selectedIndex === -1) {
                municipio.selectedIndex = 0;
            }
        } catch {
            if (!signal.aborted) {
                municipio.replaceChildren(opcao('', 'Não foi possível carregar os municípios'));
            }
        }
    }

    uf.addEventListener('change', () => {
        municipio.dataset.selecionado = '';
        carregar();
    });

    carregar();
}
