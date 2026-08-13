export default class MovimentacaoApi {
    static listar(filtro, mes) {
        return RequestHelper.call({
            url: `/api/movimentacoes?filtro=${filtro}&mes=${mes}`,
            start: false,
        });
    }

    static interpretar(texto) {
        return RequestHelper.call({
            url: '/api/movimentacoes/interpretar',
            method: 'POST',
            body: { texto },
            loadingMessage: 'Interpretando...',
        });
    }

    static salvar(data) {
        return RequestHelper.call({
            url: '/api/movimentacoes',
            method: 'POST',
            body: data,
            loadingMessage: 'Salvando...',
        });
    }

    static excluir(id) {
        return RequestHelper.call({
            url: `/api/movimentacoes/${id}`,
            method: 'DELETE',
            responseType: 'text',
            start: false,
        });
    }
}
