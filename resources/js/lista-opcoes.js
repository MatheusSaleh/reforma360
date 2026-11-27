export function criarListaOpcoes(input, lista, escolher, formatarCodigo = (codigo) => codigo) {
    let opcoes = [];
    let ativa = -1;

    const aberta = () => !lista.hidden;

    function fechar() {
        lista.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        ativa = -1;
    }

    function abrir() {
        lista.scrollTop = 0;
        lista.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        input.removeAttribute('aria-activedescendant');
        ativa = -1;
    }

    function marcarAtiva(indice) {
        lista.children[ativa]?.setAttribute('aria-selected', 'false');
        const item = lista.children[indice];
        item.setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', item.id);
        item.scrollIntoView({ block: 'nearest' });
        ativa = indice;
    }

    function selecionar(indice) {
        fechar();
        escolher(opcoes[indice]);
    }

    function mensagem(texto) {
        opcoes = [];
        const item = document.createElement('li');
        item.setAttribute('role', 'option');
        item.setAttribute('aria-disabled', 'true');
        item.className = 'lista-opcoes-mensagem';
        item.textContent = texto;
        lista.replaceChildren(item);
        abrir();
    }

    function mostrar(sugestoes, direcao = 0) {
        if (sugestoes.length === 0) {
            mensagem('Nenhum código encontrado.');
            return;
        }

        opcoes = sugestoes;
        lista.replaceChildren(...opcoes.map((opcao, indice) => {
            const item = document.createElement('li');
            item.id = `${lista.id}-${indice}`;
            item.dataset.indice = indice;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');

            if (opcao.codigo) {
                const codigo = document.createElement('span');
                codigo.className = 'lista-opcoes-codigo';
                codigo.textContent = formatarCodigo(opcao.codigo);
                item.append(codigo);
            }

            const texto = document.createElement('span');
            texto.textContent = opcao.descricao;

            if (opcao.explicacao) {
                const explicacao = document.createElement('small');
                explicacao.className = 'lista-opcoes-explicacao';
                explicacao.textContent = opcao.explicacao;
                texto.append(explicacao);
            }

            item.append(texto);
            return item;
        }));
        abrir();
        if (direcao !== 0) {
            marcarAtiva(direcao > 0 ? 0 : opcoes.length - 1);
        }
    }

    // Mantém o foco no campo até o clique confirmar a opção.
    lista.addEventListener('mousedown', (evento) => {
        if (evento.target.closest('[data-indice]')) {
            evento.preventDefault();
        }
    });
    lista.addEventListener('click', (evento) => {
        const item = evento.target.closest('[data-indice]');
        if (item) {
            selecionar(Number(item.dataset.indice));
        }
    });

    input.addEventListener('keydown', (evento) => {
        if (evento.isComposing || !aberta()) {
            return;
        }

        if (evento.key === 'Escape') {
            evento.preventDefault();
            fechar();
        } else if (opcoes.length > 0 && evento.key === 'ArrowDown') {
            evento.preventDefault();
            marcarAtiva((ativa + 1) % opcoes.length);
        } else if (opcoes.length > 0 && evento.key === 'ArrowUp') {
            evento.preventDefault();
            marcarAtiva(ativa <= 0 ? opcoes.length - 1 : ativa - 1);
        } else if (evento.key === 'Enter' && ativa >= 0) {
            evento.preventDefault();
            selecionar(ativa);
        }
    });

    input.addEventListener('blur', fechar);

    return { aberta, fechar, mensagem, mostrar };
}
