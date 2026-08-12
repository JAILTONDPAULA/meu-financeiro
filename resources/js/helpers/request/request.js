export default class RequestHelper {
    static call({
        url,
        method = 'GET',
        body = null,
        responseType = 'json',
        start = true,
        loadingMessage = 'Carregando...',
    }) {
        if (typeof window.Toast === 'undefined') {
            alert('RequestHelper depende do componente Toast, que não foi implementado/carregado. Implemente-o em resources/js/components/toast.');
            return Promise.reject(new Error('Toast não implementado'));
        }

        if (start && typeof window.Preload === 'undefined') {
            Toast.error('RequestHelper depende do componente <strong>Preload</strong>, que não foi implementado/carregado.');
            return Promise.reject(new Error('Preload não implementado'));
        }

        if (start) {
            Preload.create(loadingMessage);
        }

        const isFormData = body instanceof FormData;

        return fetch(url, {
            method,
            body: body ? (isFormData ? body : JSON.stringify(body)) : null,
            headers: body && !isFormData ? { 'Content-Type': 'application/json' } : {},
        })
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    throw text;
                });
            }

            switch (responseType) {
                case 'json':   return res.json();
                case 'text':   return res.text();
                case 'blob':   return res.blob();
                case 'buffer': return res.arrayBuffer();
                default:       return res.json();
            }
        })
        .then(data => data)
        .catch(err => {
            console.error('erro mapeado', err);
            if (start) {
                Preload.destroy();
            }

            Toast.error(err);
            throw err;
        });
    }
}

window.RequestHelper = RequestHelper;
