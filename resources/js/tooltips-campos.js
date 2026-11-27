export function iniciarTooltipsDosCampos() {
    const campos = document.querySelectorAll('.campo-com-tooltip');

    campos.forEach((campo) => {
        campo.addEventListener('pointerleave', () => {
            if (!campo.matches(':focus-within')) {
                campo.removeAttribute('data-tooltip-dispensado');
            }
        });
        campo.addEventListener('focusout', (evento) => {
            if (!campo.contains(evento.relatedTarget)) {
                campo.removeAttribute('data-tooltip-dispensado');
            }
        });
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            campos.forEach((campo) => {
                if (campo.matches(':hover, :focus-within')) {
                    campo.setAttribute('data-tooltip-dispensado', '');
                }
            });
        }
    });
}
