function soDigitos(texto) {
    return texto.replace(/\D/g, '');
}

function lerDecimal(texto) {
    const limpo = texto.trim().replace(/^R\$\s*/i, '').replace(/\s*%$/, '');
    const brasileiro = /^(\d{1,3}(?:\.\d{3})+|\d*),(\d*)$/.exec(limpo);
    if (brasileiro) {
        return [soDigitos(brasileiro[1]) || '0', brasileiro[2]];
    }

    const decimal = /^(\d+)(?:\.(\d*))?$/.exec(limpo);
    return decimal ? [decimal[1], decimal[2]] : null;
}

function moeda(digitos) {
    if (!digitos) {
        return '';
    }

    const centavos = digitos.replace(/^0+(?=\d)/, '').padStart(3, '0');
    const inteiro = centavos.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return `${inteiro},${centavos.slice(-2)}`;
}

function atualizar(campo, valor, cursor = valor.length) {
    if (campo.value !== valor) {
        campo.value = valor;
        campo.setSelectionRange(cursor, cursor);
    }
}

function limparDecimal(texto) {
    const caracteres = texto.replace(/[^\d.,]/g, '');
    const separador = caracteres.search(/[.,]/);
    if (separador === -1) {
        return caracteres;
    }

    const inteiro = caracteres.slice(0, separador) || '0';
    return `${inteiro},${soDigitos(caracteres.slice(separador + 1))}`;
}

function textoMonetario(texto) {
    const limpo = texto.trim().replace(/^R\$\s*/i, '');
    const partes = /^\d{1,3}(?:\.\d{3})+$/.test(limpo)
        ? [soDigitos(limpo)]
        : lerDecimal(limpo);

    if (!partes || (partes[1]?.length ?? 0) > 2) {
        return null;
    }

    return moeda(partes[0] + (partes[1] ?? '').padEnd(2, '0'));
}

function iniciarDecimal(campo) {
    const monetario = campo.hasAttribute('data-moeda');

    function formatarValorCompleto(texto) {
        if (monetario) {
            return textoMonetario(texto);
        }

        const partes = lerDecimal(texto);
        return partes ? partes.filter((parte) => parte !== undefined).join(',') : null;
    }

    function formatarAoDigitar(evento) {
        if (evento.isComposing) {
            return;
        }

        const texto = campo.value;
        const cursor = campo.selectionStart;
        if (!monetario) {
            atualizar(campo, limparDecimal(texto), limparDecimal(texto.slice(0, cursor)).length);
            return;
        }

        const digitos = soDigitos(texto);
        const apagando = evento.inputType?.startsWith('delete');
        const aDireita = soDigitos(texto.slice(cursor)).length;
        const valor = apagando && !aDireita && /^0+$/.test(digitos) ? '' : moeda(digitos);
        let posicao = valor.length;
        for (let restantes = aDireita; restantes > 0 && posicao > 0;) {
            if (/\d/.test(valor[--posicao])) {
                restantes--;
            }
        }
        if (apagando && evento.inputType.endsWith('Backward') && /[.,]/.test(valor[posicao - 1] ?? '')) {
            posicao--;
        }
        atualizar(campo, valor, posicao);
    }

    atualizar(campo, formatarValorCompleto(campo.value) ?? campo.value);
    campo.addEventListener('input', formatarAoDigitar);
    campo.addEventListener('compositionend', formatarAoDigitar);
    if (monetario) {
        document.getElementById(`${campo.name}-ajuda-moeda`)?.removeAttribute('hidden');
    } else {
        campo.addEventListener('blur', () => atualizar(campo, campo.value.replace(/,$/, '')));
    }
    campo.addEventListener('paste', (evento) => {
        if (!evento.clipboardData) {
            return;
        }

        evento.preventDefault();
        const texto = evento.clipboardData.getData('text').trim();
        const selecionouTudo = campo.selectionStart === 0 && campo.selectionEnd === campo.value.length;
        if (selecionouTudo) {
            const formatado = formatarValorCompleto(texto);
            if (formatado !== null) {
                atualizar(campo, formatado);
            }
        } else if (/^\d+$/.test(texto)) {
            campo.setRangeText(texto, campo.selectionStart, campo.selectionEnd, 'end');
            formatarAoDigitar(evento);
        }
    });
}

function iniciarDigitos(campo) {
    const limite = Number(campo.dataset.digitos ?? Infinity);
    const formatar = (texto) => soDigitos(texto).slice(0, limite);
    atualizar(campo, formatar(campo.value));
    function formatarAoDigitar(evento) {
        if (!evento.isComposing) {
            atualizar(campo, formatar(campo.value), formatar(campo.value.slice(0, campo.selectionStart)).length);
        }
    }
    campo.addEventListener('input', formatarAoDigitar);
    campo.addEventListener('compositionend', formatarAoDigitar);
}

export function iniciarEntradasNumericas() {
    document.querySelectorAll('input[inputmode="decimal"]').forEach(iniciarDecimal);
    document.querySelectorAll('input[inputmode="numeric"]').forEach(iniciarDigitos);
}
