# RequestHelper

Helper estático para chamadas `fetch`, com loading e feedback de erro automáticos. É registrado globalmente em `window.RequestHelper` a partir de `resources/js/app.js`, que carrega antes de qualquer página — **não precisa ser importado novamente** em `resources/js/pages/*.js`.

## Dependências obrigatórias

`RequestHelper` **não funciona sozinho**. Ele depende dos componentes:

- [`Toast`](../../components/toast/README.md) — usado para reportar qualquer erro de requisição, sempre.
- [`Preload`](../../components/preload/README.md) — usado para exibir o loading, apenas quando `start: true` (padrão).

Ambos precisam estar importados/registrados em `app.js` **antes** deste helper (é o caso por padrão neste projeto). Se `Toast` não existir, `RequestHelper.call()` interrompe a chamada e avisa via `alert()` nativo (já que não há Toast pra avisar). Se `Preload` não existir e a chamada exigir loading (`start: true`), a chamada é interrompida e o aviso é feito via `Toast.error(...)`. Em ambos os casos a Promise retornada é rejeitada e o `fetch` **nunca chega a ser disparado** — isso é proposital, para deixar claro no desenvolvimento que falta implementar/carregar o componente, em vez de falhar silenciosamente.

## Uso

```js
RequestHelper.call({
    url: '/api/transacoes',
    method: 'POST',
    body: { valor: 100, descricao: 'Salário' },
})
.then(data => {
    // data já vem no formato de responseType (json por padrão)
})
.catch(err => {
    // erro já foi mostrado via Toast; aqui só decide o que fazer no fluxo
});
```

## Parâmetros de `RequestHelper.call({ ... })`

| Parâmetro | Padrão | Descrição |
| --- | --- | --- |
| `url` | — (obrigatório) | URL da requisição. |
| `method` | `'GET'` | Verbo HTTP. |
| `body` | `null` | Objeto a ser enviado como JSON no corpo. Quando informado, define automaticamente o header `Content-Type: application/json`. |
| `responseType` | `'json'` | Como o corpo da resposta é lido: `'json'`, `'text'`, `'blob'` (binário, ex: imagem/PDF) ou `'buffer'` (`ArrayBuffer` bruto). |
| `start` | `true` | Se `true`, exibe `Preload.create(loadingMessage)` no início e `Preload.destroy()` ao final/erro. Se `false`, a chamada não mexe no Preload (e não exige que ele esteja carregado). |
| `loadingMessage` | `'Carregando...'` | Mensagem exibida no Preload enquanto a requisição está em andamento (só usada quando `start: true`). |

## Erros

Qualquer status HTTP fora da faixa 2xx, ou falha de rede, cai no mesmo fluxo: o Preload (se ativo) é destruído, um `Toast.error(...)` é disparado com o método, a URL e a mensagem do erro em HTML, e o erro original é relançado (`throw`) sem ser encapsulado — quem chamar `RequestHelper.call()` pode tratar com `.catch()`/`try+catch` normalmente.
