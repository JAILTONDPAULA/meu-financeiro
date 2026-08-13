import './dialog.sass';

export default class Dialog {
    static events = {
        init() {
            $(document)
                .on('click', '.dialog .fechar', event => Dialog.fecharElemento($(event.currentTarget).closest('.dialog')))
                .on('mousedown', '.dialog', event => Dialog.fecharFundo(event))
                .on('keydown', event => Dialog.fecharEscape(event));
        },
    };

    /**
     * Monta o diálogo e devolve o id informado.
     *
     * titulo  texto do cabeçalho, ao lado do X de fechar
     * id      vai no <form>; é por ele que a página estiliza o body e ouve o submit
     * body    HTML injetado no corpo
     * submit  false (padrão) ou o texto do botão de enviar
     * tipo    'error' aplica os tons vermelhos, inclusive no botão de enviar
     */
    static create({ titulo = '', id, body = '', submit = false, tipo = null }) {
        const $dialog = $(
            `<section class="dialog${tipo ? ` dialog-${tipo}` : ''}">` +
                `<form id="${id}">` +
                    '<header>' +
                        '<h2></h2>' +
                        '<button type="button" class="fechar"><i class="fa-solid fa-xmark"></i></button>' +
                    '</header>' +
                    '<div class="body"></div>' +
                    '<footer>' +
                        '<button type="button" class="fechar">Fechar</button>' +
                    '</footer>' +
                '</form>' +
            '</section>'
        );

        $dialog.find('h2').text(titulo);
        $dialog.find('.body').html(body);

        if (submit) {
            $dialog.find('footer').append($('<button type="submit" class="enviar"></button>').text(submit));
        }

        $('body').append($dialog).css('overflow', 'hidden');

        return id;
    }

    static close(id) {
        Dialog.fecharElemento($(`#${id}`).closest('.dialog'));
    }

    static closeAll() {
        $('.dialog').each((_, elemento) => Dialog.fecharElemento($(elemento)));
    }

    static fecharElemento($dialog) {
        if (!$dialog.length || $dialog.hasClass('is-closing')) {
            return;
        }

        $dialog.addClass('is-closing').one('animationend', _ => {
            $dialog.remove();

            if (!$('.dialog').length) {
                $('body').css('overflow', '');
            }
        });
    }

    // Clique no fundo escuro, fora do formulário.
    static fecharFundo(event) {
        if (event.target !== event.currentTarget) {
            return;
        }

        Dialog.fecharElemento($(event.currentTarget));
    }

    // Escape fecha só o diálogo do topo da pilha.
    static fecharEscape(event) {
        if (event.key !== 'Escape') {
            return;
        }

        Dialog.fecharElemento($('.dialog').last());
    }
}

window.Dialog = Dialog;
