import './iframe.sass';

export default class Iframe {
    static events = {
        init() {
            window.addEventListener('message', Iframe.onMessage);
            Iframe.bindEscape();
        },
    };

    static create(url, dimensoes = null) {
        const id = `iframe-${Date.now()}${Math.floor(Math.random() * 1000)}`;

        const $iframe = $(
            `<div class="iframe" id="${id}" style="${Iframe.style(dimensoes)}">` +
                `<iframe src="${Iframe.url(url, id)}"></iframe>` +
            '</div>'
        );

        $('body').append($iframe).css('overflow', 'hidden');
        $iframe.find('iframe').on('load', Iframe.bindRefresh);

        return id;
    }

    static close(id) {
        const $iframe = $(`#${id}`);

        if (!$iframe.length) {
            return;
        }

        $iframe.addClass('is-closing').one('animationend', _ => {
            $iframe.remove();

            if (!$('.iframe').length) {
                $('body').css('overflow', '');
            }
        });
    }

    static closeAll() {
        $('.iframe').each((_, el) => Iframe.close(el.id));
    }

    static onMessage(event) {
        const { type, id } = event.data || {};

        if (type === 'closeAll') {
            Iframe.closeAll();
            return;
        }

        if (type === 'close') {
            Iframe.close(id);
        }
    }

    static bindEscape() {
        const id = new URLSearchParams(location.search).get('iframe_id');

        if (!id) {
            return;
        }

        $(document).on('keydown', e => {
            if (e.key !== 'Escape') {
                return;
            }

            parent.postMessage({ type: 'close', id }, '*');
        });
    }

    static bindRefresh() {
        const janela = this.contentWindow;

        $(janela.document).on('keydown', e => {
            if (e.key !== 'F5' && !(e.ctrlKey && e.key.toLowerCase() === 'r')) {
                return;
            }

            e.preventDefault();
            janela.location.reload();
        });
    }

    static url(url, id) {
        return `${url}${url.includes('?') ? '&' : '?'}iframe_id=${id}`;
    }

    static style(dimensoes) {
        if (!dimensoes) {
            return '';
        }

        const { t = 0, r = 0, b = 0, l = 0 } = dimensoes;

        return `top: ${t}px; left: ${l}px; width: calc(100vw - ${l + r}px); height: calc(100dvh - ${t + b}px);`;
    }
}

window.Iframe = Iframe;
