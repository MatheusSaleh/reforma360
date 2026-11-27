import { criarListaOpcoes } from './lista-opcoes';

function iniciarCampo(input, sugestoes) {
    const controle = input.parentElement;
    const botao = controle.querySelector('button');
    const lista = controle.querySelector('[role="listbox"]');
    const dropdown = criarListaOpcoes(input, lista, ({ codigo }) => {
        input.value = codigo;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    const mostrar = ({ reabrindo = false, direcao = 0 } = {}) => {
        const termo = input.value.trim();
        const opcoes = sugestoes();
        const filtradas = opcoes.filter(({ codigo }) => codigo.startsWith(termo));
        const codigoCompleto = filtradas.some(({ codigo }) => codigo === termo);
        dropdown.mostrar(reabrindo && codigoCompleto ? opcoes : filtradas, direcao);
    };

    input.addEventListener('input', () => mostrar());
    input.addEventListener('click', () => {
        if (!dropdown.aberta()) {
            mostrar({ reabrindo: true });
        }
    });

    input.addEventListener('keydown', (evento) => {
        if (!evento.defaultPrevented && !evento.isComposing && ['ArrowDown', 'ArrowUp'].includes(evento.key)) {
            evento.preventDefault();
            mostrar({ reabrindo: true, direcao: evento.key === 'ArrowDown' ? 1 : -1 });
        }
    });

    botao.addEventListener('mousedown', (evento) => evento.preventDefault());
    botao.addEventListener('click', () => {
        input.focus();
        if (dropdown.aberta()) {
            dropdown.fechar();
        } else {
            dropdown.mostrar(sugestoes());
        }
    });

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', lista.id);
    botao.hidden = false;
}

async function iniciar(raiz) {
    const cst = raiz.querySelector('[name="cst"]');
    const cClassTrib = raiz.querySelector('[name="c_class_trib"]');
    const descricao = raiz.querySelector('[data-c-class-trib-descricao]');

    let situacoes;

    try {
        const resposta = await fetch(raiz.dataset.url, { headers: { Accept: 'application/json' } });

        if (!resposta.ok) {
            return;
        }

        situacoes = await resposta.json();
    } catch {
        return;
    }

    const explicacoes = JSON.parse(raiz.dataset.explicacoesCst);
    const sugestoesCst = situacoes.map(({ codigo, descricao }) => ({ codigo, descricao, explicacao: explicacoes[codigo] }));
    const classificacoes = situacoes.flatMap((situacao) => situacao.classificacoes);

    iniciarCampo(cst, () => sugestoesCst);
    iniciarCampo(cClassTrib, () => {
        const escolhido = situacoes.find((situacao) => situacao.codigo === cst.value.trim());
        return escolhido ? escolhido.classificacoes : classificacoes;
    });

    function atualizarDescricao() {
        const encontrada = classificacoes.find(({ codigo }) => codigo === cClassTrib.value.trim());
        descricao.textContent = encontrada?.descricao ?? '';
    }

    cClassTrib.addEventListener('input', atualizarDescricao);
    cClassTrib.addEventListener('change', atualizarDescricao);
    atualizarDescricao();
}

export function iniciarClassificacoes() {
    document.querySelectorAll('[data-classificacoes]').forEach(iniciar);
}
