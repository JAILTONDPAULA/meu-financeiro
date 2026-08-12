import './preload.sass';

export default class Preload {
    static #element = null;

    static create(message = 'Carregando...') {
        if (Preload.#element) {
            Preload.message(message);
            return Preload.#element;
        }

        const $preload = $(
            '<div class="preload">' +
                '<section class="background">' +
                    '<div></div>' +
                    '<div></div>' +
                    '<div></div>' +
                '</section>' +
                '<div class="content">' +
                    '<div class="header">' +
                        '<h1>Meu Financeiro</h1>' +
                        `<p>${message}</p>` +
                    '</div>' +
                    '<div class="loading-bar">' +
                        '<span></span>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append($preload);
        Preload.#element = $preload;

        return $preload;
    }

    static destroy() {
        if (!Preload.#element) {
            return;
        }

        const $preload = Preload.#element;
        Preload.#element = null;

        $preload.addClass('is-leaving');
        setTimeout(() => $preload.remove(), 300);
    }

    static message(text) {
        if (!Preload.#element) {
            return;
        }

        Preload.#element.find('.content p').text(text);
    }
}

window.Preload = Preload;
