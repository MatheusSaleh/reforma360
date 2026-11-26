/**
 * Carrega os municípios da UF selecionada no formulário de simulação.
 */
function iniciarSeletorDeMunicipio() {
    const uf = document.querySelector('[data-uf]');
    const municipio = document.querySelector('[data-municipio]');

    if (!uf || !municipio) {
        return;
    }

    const opcao = (valor, texto) => {
        const elemento = document.createElement('option');
        elemento.value = valor;
        elemento.textContent = texto;
        return elemento;
    };

    async function carregar() {
        municipio.replaceChildren(opcao('', uf.value ? 'Carregando...' : 'Selecione a UF primeiro'));

        if (!uf.value) {
            return;
        }

        try {
            const resposta = await fetch(municipio.dataset.url.replace('__UF__', uf.value), {
                headers: { Accept: 'application/json' },
            });

            if (!resposta.ok) {
                throw new Error(String(resposta.status));
            }

            const lista = await resposta.json();
            municipio.replaceChildren(opcao('', 'Selecione'), ...lista.map((m) => opcao(m.codigo, m.nome)));
            municipio.value = municipio.dataset.selecionado || '';
        } catch {
            municipio.replaceChildren(opcao('', 'Não foi possível carregar os municípios'));
        }
    }

    uf.addEventListener('change', () => {
        municipio.dataset.selecionado = '';
        carregar();
    });

    carregar();
}

iniciarSeletorDeMunicipio();
