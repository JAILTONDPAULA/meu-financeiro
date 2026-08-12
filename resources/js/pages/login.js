import '../../sass/pages/login.sass';
import AuthApi from '../apis/auth';
class Page {
    static events = {
        init() {
            Page.events.dom();
            Preload.destroy();
        },

        dom() {
            $(document).on('change', '#view-password input', Page.showPassword);
            $(document).on('submit', '#login-form', Page.login);
        },
    };

    static showPassword() {
        const $input = $(this).closest('.input-group').find('>input');
        if ($input.attr('type') === 'text') {
            $input.attr('type', 'password');
            return;
        } else {
            $input.attr('type', 'text');
            return;
        }
    }

    static login(e) {
        e.preventDefault();
        const data = new FormData(e.target);
        AuthApi.login(data).then(r => location.href = '/');
    }
}

$(document).ready(_ => Page.events.init());
