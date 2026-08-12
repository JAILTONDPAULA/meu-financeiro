import './menu.sass';
import AuthApi from '../../apis/auth.js';

export default class Menu {
    static events = {
        init() {
            Menu.events.dom();
        },

        dom() {
            $(document).on('click', '#menu-toggle', Menu.open);
            $(document).on('click', '#sidebar-close, #sidebar-overlay', Menu.close);
            $(document).on('click', '#btn-logout', Menu.logout);
        },
    };

    static open() {
        $('#sidebar, #sidebar-overlay').addClass('is-open');
    }

    static close() {
        $('#sidebar, #sidebar-overlay').removeClass('is-open');
    }

    static logout() {
        AuthApi.logout().then(_ => location.href = '/login');
    }
}

window.Menu = Menu;
