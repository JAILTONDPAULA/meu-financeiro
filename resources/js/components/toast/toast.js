import './toast.sass';

const ICONS = {
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-xmark',
    warning: 'fa-solid fa-triangle-exclamation',
    info: 'fa-solid fa-circle-info',
};

export default class Toast {
    static #container = null;

    static show({ type = 'info', message = '', duration = 5000 } = {}) {
        const $container = Toast.#getContainer();
        const icon = ICONS[type] || ICONS.info;

        const $toast = $(
            `<div class="toast toast-${type}">` +
                `<div class="icon"><i class="${icon}"></i></div>` +
                '<div class="message"></div>' +
                '<button type="button" class="close"><i class="fa-solid fa-xmark"></i></button>' +
            '</div>'
        );

        $toast.find('.message').html(message);
        $toast.on('click', '.close', () => Toast.#close($toast));
        $container.append($toast);

        if (duration > 0) {
            setTimeout(() => Toast.#close($toast), duration);
        }

        return $toast;
    }

    static success(message, duration = 5000) {
        return Toast.show({ type: 'success', message, duration });
    }

    static error(message, duration = 5000) {
        return Toast.show({ type: 'error', message, duration });
    }

    static warning(message, duration = 5000) {
        return Toast.show({ type: 'warning', message, duration });
    }

    static info(message, duration = 5000) {
        return Toast.show({ type: 'info', message, duration });
    }

    static #getContainer() {
        if (Toast.#container) {
            return Toast.#container;
        }

        const $container = $('<div class="toast-container"></div>');
        $('body').append($container);
        Toast.#container = $container;

        return $container;
    }

    static #close($toast) {
        $toast.addClass('is-leaving');
        setTimeout(() => $toast.remove(), 300);
    }
}

window.Toast = Toast;
