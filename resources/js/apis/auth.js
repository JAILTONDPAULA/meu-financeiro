export default class AuthApi {
    static login(data) {
        return RequestHelper.call({
            url: '/api/login',
            method: 'POST',
            body: data,
            responseType: 'text',
        });
    }

    static logout() {
        return RequestHelper.call({
            url: '/api/logout',
            method: 'POST',
        });
    }
}
