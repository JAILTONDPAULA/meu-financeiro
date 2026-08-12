import '../../sass/pages/home.sass';

class Page {
    static events = {
        init() {
            Page.events.dom();
            Preload.destroy();
        },
        dom() {

        }
    }
}

$(document).ready(_=>Page.events.init());