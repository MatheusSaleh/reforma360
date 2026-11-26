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

    let opcoes = [];
    let ativa = -1;
    let requisicao = null;
    let requisicaoDetalhe = null;

    const aberta = () => !lista.hidden;

    function fechar() {
        lista.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        ativa = -1;
    }

    function marcarAtiva(indice) {
        const itens = lista.querySelectorAll('[role="option"]');
        itens.forEach((item, i) => item.setAttribute('aria-selected', String(i === indice)));
        ativa = indice;

        if (indice >= 0 && itens[indice]) {
            input.setAttribute('aria-activedescendant', itens[indice].id);
            itens[indice].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    function mensagem(texto) {
        const item = document.createElement('li');
        item.className = 'px-3 py-2 text-slate-500';
        item.textContent = texto;
        lista.replaceChildren(item);
        lista.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function renderizar() {
        if (opcoes.length === 0) {
            mensagem('Nenhum código encontrado.');
            return;
        }

        lista.replaceChildren(...opcoes.map((opcao, i) => {
            const item = document.createElement('li');
            item.id = `${lista.id}-${i}`;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.className = 'cursor-pointer px-3 py-2 hover:bg-emerald-50 aria-selected:bg-emerald-100';

            const codigo = document.createElement('span');
            codigo.className = 'mr-2 font-mono font-semibold text-slate-900';
            codigo.textContent = formatarCodigo(tipo, opcao.codigo);

            const descricao = document.createElement('span');
            descricao.className = 'text-slate-700';
            descricao.textContent = opcao.descricao;

            item.append(codigo, descricao);
            item.addEventListener('mousedown', (evento) => {
                evento.preventDefault();
                escolher(i);
            });

            return item;
        }));

        lista.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        marcarAtiva(-1);
    }

    async function buscar(termo) {
        requisicao?.abort();
        requisicao = new AbortController();

        try {
            const url = new URL(raiz.dataset.urlBusca, window.location.origin);
            url.searchParams.set('q', termo);
            const resposta = await fetch(url, { headers: { Accept: 'application/json' }, signal: requisicao.signal });

            if (!resposta.ok) {
                throw new Error(String(resposta.status));
            }

            opcoes = await resposta.json();
            renderizar();
        } catch (erro) {
            if (erro.name !== 'AbortError') {
                mensagem('Não foi possível buscar agora. Digite o código manualmente.');
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
            return;
        }

        requisicaoDetalhe = new AbortController();

        try {
            const resposta = await fetch(raiz.dataset.urlDetalhe.replace('__CODIGO__', codigo), {
                headers: { Accept: 'application/json' },
                signal: requisicaoDetalhe.signal,
            });

            if (resposta.status === 404) {
                detalhe.textContent = 'Código não encontrado na tabela vigente.';
                detalhe.classList.add('text-amber-700');
                return;
            }

            if (!resposta.ok) {
                return;
            }

            const dados = await resposta.json();
            detalhe.textContent = dados.niveis.join(' › ')
                + (dados.tributado_pelo_imposto_seletivo ? ' · Sujeito ao Imposto Seletivo' : '');
        } catch {
            // Detalhe é informativo: se falhar, o campo continua funcionando.
        }
    }

    function escolher(indice) {
        const opcao = opcoes[indice];

        if (!opcao) {
            return;
        }

        input.value = opcao.codigo;
        fechar();
        carregarDetalhe(opcao.codigo);
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const aoDigitar = debounce(() => {
        const termo = input.value.trim();
        const somenteDigitos = termo.replace(/\D/g, '');

        if (/^[\d.\-\s]+$/.test(termo) && somenteDigitos.length === digitos) {
            fechar();
            carregarDetalhe(somenteDigitos);
            return;
        }

        if (termo.length < MINIMO_CARACTERES) {
            requisicao?.abort();
            fechar();
            return;
        }

        buscar(termo);
    }, ATRASO_MS);

    input.addEventListener('input', () => {
        limparDetalhe();
        aoDigitar();
    });

    input.addEventListener('keydown', (evento) => {
        if (!aberta() || opcoes.length === 0) {
            return;
        }

        if (evento.key === 'ArrowDown') {
            evento.preventDefault();
            marcarAtiva((ativa + 1) % opcoes.length);
        } else if (evento.key === 'ArrowUp') {
            evento.preventDefault();
            marcarAtiva(ativa <= 0 ? opcoes.length - 1 : ativa - 1);
        } else if (evento.key === 'Enter' && ativa >= 0) {
            evento.preventDefault();
            escolher(ativa);
        } else if (evento.key === 'Escape') {
            fechar();
        }
    });

    input.addEventListener('blur', () => setTimeout(fechar, 150));

    carregarDetalhe(input.value.replace(/\D/g, ''));
}

export function iniciarAutocompleteNomenclatura() {
    document.querySelectorAll('[data-autocomplete-nomenclatura]').forEach(iniciarCampo);
}
