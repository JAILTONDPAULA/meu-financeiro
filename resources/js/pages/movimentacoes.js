import '../../sass/pages/movimentacoes.sass';

class Page {
    static events = {
        init() {
            Page.events.dom();
            Preload.destroy();
        },

        dom() {
            $(document).on('click', '.tabs button', Page.toggleTab);
            $(document).on('click', '.filtro-data button', Page.toggleFiltroData);
        },
    };

    static toggleTab() {
        $('.tabs button').removeClass('is-active');
        $(this).addClass('is-active');
    }

    static toggleFiltroData() {
        $('.filtro-data button').removeClass('is-active');
        $(this).addClass('is-active');
    }
}

$(document).ready(_ => Page.events.init());
