/**
 * Sugestões de CST e cClassTrib (datalist) a partir dos dados abertos.
 * Sem JS, os campos continuam funcionando como texto.
 */

function opcao(valor, rotulo) {
    const elemento = document.createElement('option');
    elemento.value = valor;
    elemento.label = rotulo;
    elemento.textContent = rotulo;
    return elemento;
}

async function iniciar(raiz) {
    const cst = raiz.querySelector('[data-cst]');
    const cClassTrib = raiz.querySelector('[data-c-class-trib]');
    const descricao = raiz.querySelector('[data-c-class-trib-descricao]');
    const listaCst = document.getElementById(cst.getAttribute('list'));
    const listaClassificacoes = document.getElementById(cClassTrib.getAttribute('list'));

    let situacoes = [];

    try {
        const resposta = await fetch(raiz.dataset.url, { headers: { Accept: 'application/json' } });

        if (!resposta.ok) {
            return;
        }

        situacoes = await resposta.json();
    } catch {
        return;
    }

    listaCst.replaceChildren(...situacoes.map((s) => opcao(s.codigo, `${s.codigo} · ${s.descricao}`)));

    function classificacoesDoCst() {
        const escolhido = situacoes.find((s) => s.codigo === cst.value.trim());
        return escolhido ? escolhido.classificacoes : situacoes.flatMap((s) => s.classificacoes);
    }

    function atualizarClassificacoes() {
        listaClassificacoes.replaceChildren(
            ...classificacoesDoCst().map((c) => opcao(c.codigo, `${c.codigo} · ${c.descricao}`)),
        );
        atualizarDescricao();
    }

    function atualizarDescricao() {
        const codigo = cClassTrib.value.trim();
        const encontrada = situacoes.flatMap((s) => s.classificacoes).find((c) => c.codigo === codigo);
        descricao.textContent = encontrada ? encontrada.descricao : '';
    }

    cst.addEventListener('input', atualizarClassificacoes);
    cClassTrib.addEventListener('input', atualizarDescricao);
    atualizarClassificacoes();
}

export function iniciarClassificacoes() {
    document.querySelectorAll('[data-classificacoes]').forEach(iniciar);
}
