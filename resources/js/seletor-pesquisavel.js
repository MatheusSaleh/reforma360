import { criarListaOpcoes } from './lista-opcoes';

function normalizar(texto) {
    return texto.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLocaleLowerCase('pt-BR');
}

function iniciar(raiz) {
    const select = raiz.querySelector('select');
    const input = raiz.querySelector('input');
    const botao = raiz.querySelector('button');
    const lista = raiz.querySelector('[role="listbox"]');
    const dropdown = criarListaOpcoes(input, lista, ({ valor }) => {
        const anterior = select.value;
        select.value = valor;
        sincronizar();
        if (anterior !== valor) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    function sincronizar() {
        dropdown.fechar();
        input.value = select.selectedOptions[0]?.label ?? '';
        input.disabled = botao.disabled = select.disabled || (select.options.length === 1 && select.value === '');
    }

    function pesquisar(direcao = 0) {
        const termo = normalizar(input.value.trim());
        const opcoes = Array.from(select.options)
            .filter((opcao) => !opcao.disabled && normalizar(`${opcao.label} ${opcao.value}`).includes(termo))
            .map((opcao) => ({ valor: opcao.value, descricao: opcao.label }));

        if (opcoes.length === 0) {
            dropdown.mensagem('Nenhuma opção encontrada.');
        } else {
            dropdown.mostrar(opcoes, direcao);
        }
    }

    function abrir(direcao = 0) {
        input.value = '';
        pesquisar(direcao);
    }

    input.addEventListener('focus', () => input.select());
    input.addEventListener('click', () => {
        if (!dropdown.aberta()) {
            abrir();
        }
    });
    input.addEventListener('input', () => pesquisar(1));
    input.addEventListener('blur', sincronizar);
    input.addEventListener('keydown', (evento) => {
        if (evento.isComposing) {
            return;
        }

        if (evento.key === 'Escape' && evento.defaultPrevented) {
            sincronizar();
            input.select();
        } else if (!dropdown.aberta() && !evento.defaultPrevented && ['ArrowDown', 'ArrowUp'].includes(evento.key)) {
            evento.preventDefault();
            abrir(evento.key === 'ArrowDown' ? 1 : -1);
        } else if (evento.key === 'Enter' && dropdown.aberta()) {
            evento.preventDefault();
        }
    });

    botao.addEventListener('mousedown', (evento) => evento.preventDefault());
    botao.addEventListener('click', () => {
        if (dropdown.aberta()) {
            sincronizar();
        } else {
            input.focus();
            abrir();
        }
    });

    select.addEventListener('change', sincronizar);
    new MutationObserver(sincronizar).observe(select, { childList: true, attributes: true, attributeFilter: ['disabled'] });

    sincronizar();
    raiz.querySelector('label').htmlFor = input.id;
    select.hidden = true;
    input.hidden = false;
    botao.hidden = false;
}

export function iniciarSeletoresPesquisaveis() {
    document.querySelectorAll('[data-seletor-pesquisavel]').forEach(iniciar);
}
