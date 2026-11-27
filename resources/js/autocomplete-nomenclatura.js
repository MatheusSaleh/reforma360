import { criarListaOpcoes } from './lista-opcoes';

/**
 * Autocomplete de NCM/NBS: busca com debounce nos dados abertos da calculadora e,
 * ao escolher um código, mostra a hierarquia (capítulo › posição › ...) abaixo do campo.
 */

const ATRASO_MS = 300;
const MINIMO_CARACTERES = 2;

function formatarCodigo(tipo, codigo) {
    return tipo === 'ncm'
        ? codigo.replace(/^(\d{4})(\d{2})(\d{2})$/, '$1.$2.$3')
        : codigo.replace(/^(\d)(\d{4})(\d{2})(\d{2})$/, '$1.$2.$3.$4');
}

function debounce(funcao, atraso) {
    let temporizador;
    return (...args) => {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => funcao(...args), atraso);
    };
}

function iniciarCampo(raiz) {
    const tipo = raiz.dataset.tipo;
    const digitos = Number(raiz.dataset.digitos);
    const input = raiz.querySelector('input[role="combobox"]');
    const lista = raiz.querySelector('[role="listbox"]');
    const detalhe = raiz.querySelector('[data-detalhe]');

    const formulario = input.form;
    const camposIs = tipo === 'ncm' ? formulario?.querySelector('[data-campos-is]') : null;
    const dataSimulacao = camposIs ? formulario.querySelector('input[name="data"]') : null;

    function mostrarCamposIs(visivel) {
        if (camposIs) {
            camposIs.hidden = !visivel;
            camposIs.disabled = !visivel;
        }
    }

    let requisicao = null;
    let requisicaoDetalhe = null;

    const dropdown = criarListaOpcoes(input, lista, escolher, (codigo) => formatarCodigo(tipo, codigo));

    async function buscar(termo) {
        requisicao = new AbortController();
        const { signal } = requisicao;

        try {
            const url = new URL(raiz.dataset.urlBusca, window.location.origin);
            url.searchParams.set('q', termo);
            const resposta = await fetch(url, { headers: { Accept: 'application/json' }, signal });

            if (!resposta.ok) {
                throw new Error(String(resposta.status));
            }

            const opcoes = await resposta.json();
            if (!signal.aborted && document.activeElement === input) {
                dropdown.mostrar(opcoes);
            }
        } catch (erro) {
            if (erro.name !== 'AbortError' && !signal.aborted && document.activeElement === input) {
                dropdown.mensagem('Não foi possível buscar agora. Digite o código manualmente.');
            }
        }
    }

    function limparDetalhe() {
        requisicaoDetalhe?.abort();
        detalhe.textContent = '';
        detalhe.classList.remove('text-amber-700');
    }

    async function carregarDetalhe(codigo) {
        limparDetalhe();

        if (codigo.length !== digitos) {
            mostrarCamposIs(false);
            return;
        }

        requisicaoDetalhe = new AbortController();

        try {
            const url = new URL(raiz.dataset.urlDetalhe.replace('__CODIGO__', codigo), window.location.origin);
            const data = dataSimulacao?.value || camposIs?.dataset.data;
            if (data) {
                url.searchParams.set('data', data);
            }

            const { signal } = requisicaoDetalhe;
            const resposta = await fetch(url, { headers: { Accept: 'application/json' }, signal });

            if (signal.aborted) {
                return;
            }

            if (resposta.status === 404) {
                mostrarCamposIs(false);
                detalhe.textContent = 'Código não encontrado na tabela vigente.';
                detalhe.classList.add('text-amber-700');
                return;
            }

            if (!resposta.ok) {
                mostrarCamposIs(true);
                return;
            }

            const dados = await resposta.json();
            if (signal.aborted) {
                return;
            }

            mostrarCamposIs(dados.tributado_pelo_imposto_seletivo);
            detalhe.textContent = dados.niveis.join(' › ')
                + (dados.tributado_pelo_imposto_seletivo ? ' · Sujeito ao Imposto Seletivo' : '');
        } catch (erro) {
            if (erro.name !== 'AbortError') {
                mostrarCamposIs(true);
            }
        }
    }

    function escolher({ codigo }) {
        input.value = codigo;
        carregarDetalhe(codigo);
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const aoDigitar = debounce(() => {
        const termo = input.value.trim();
        const somenteDigitos = termo.replace(/\D/g, '');

        if (/^[\d.\-\s]+$/.test(termo) && somenteDigitos.length === digitos) {
            dropdown.fechar();
            carregarDetalhe(somenteDigitos);
            return;
        }

        if (termo.length < MINIMO_CARACTERES) {
            dropdown.fechar();
            return;
        }

        if (document.activeElement === input) {
            buscar(termo);
        }
    }, ATRASO_MS);

    input.addEventListener('input', () => {
        limparDetalhe();
        requisicao?.abort();
        dropdown.fechar();
        aoDigitar();
    });

    input.addEventListener('blur', () => requisicao?.abort());

    const atualizarDetalhe = () => carregarDetalhe(input.value.replace(/\D/g, ''));
    dataSimulacao?.addEventListener('change', atualizarDetalhe);
    atualizarDetalhe();
}

export function iniciarAutocompleteNomenclatura() {
    document.querySelectorAll('[data-autocomplete-nomenclatura]').forEach(iniciarCampo);
}
