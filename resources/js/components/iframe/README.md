# Iframe

Abre uma página do sistema dentro de um iframe sobreposto (`fixed`, `z-index: 9`), com animação de entrada da esquerda para a direita. É registrado globalmente em `window.Iframe` a partir de `resources/js/app.js`, que também chama `Iframe.events.init()` — o `init` liga o listener de `message`, que é como os iframes filhos pedem para serem fechados.

## Uso

```js
// ocupa a tela inteira (100vw x 100dvh)
Iframe.create('/movimentacoes');

// com distância das bordas
Iframe.create('/movimentacoes', { t: 40, r: 40, b: 40, l: 40 });

// guardando o id para fechar programaticamente pelo pai
const id = Iframe.create('/movimentacoes');
Iframe.close(id);

// fecha todos os iframes abertos na página
Iframe.closeAll();
```

## Métodos estáticos

| Método | Descrição |
| --- | --- |
| `Iframe.create(url, dimensoes = null)` | Cria o iframe e devolve o `id` gerado. Sem `dimensoes`, ocupa `100vw x 100dvh`. |
| `Iframe.close(id)` | Roda a animação reversa e remove o elemento ao final dela (`animationend`). |
| `Iframe.closeAll()` | Fecha todos os iframes criados por este componente. |
| `Iframe.events.init()` | Liga o listener de `message`. Chamado uma vez em `app.js`. |

## Dimensões `{ t, r, b, l }`

Cada chave é um lado (top, right, bottom, left) e o valor, em pixels, é ao mesmo tempo **a distância daquela borda** e **o quanto é subtraído do tamanho**:

- `top: t` · `left: l`
- `width: calc(100vw - (l + r))` · `height: calc(100dvh - (t + b))`

Chaves omitidas valem `0`.

## Fechar a partir da página filha

Ao criar o iframe, o `id` é anexado na URL como `?iframe_id=...`. A página filha lê esse parâmetro e devolve para o pai via `postMessage`:

```js
// dentro da página aberta no iframe
const id = new URLSearchParams(location.search).get('iframe_id');

parent.postMessage({ type: 'close', id }, '*');  // fecha só este
parent.postMessage({ type: 'closeAll' }, '*');   // fecha todos
```

## Comportamentos

- **Esc dentro do iframe**: fecha o próprio iframe, sem precisar de nenhum código na página filha. Como toda página do sistema carrega o `app.js` (e portanto este componente), o `init()` verifica se existe `?iframe_id=` na URL — se existir, a página está rodando dentro de um iframe deste componente e liga o `Esc` para mandar `{ type: 'close', id }` ao pai via `postMessage`.
- **Scroll do pai**: ao abrir, aplica `overflow: hidden` no `body`. Ao fechar, só devolve o scroll quando não sobrar nenhum iframe deste componente na página.
- **F5 / Ctrl+R dentro do iframe**: interceptados no `document` do iframe e redirecionados para `contentWindow.location.reload()`, então recarregam apenas o iframe, não a página pai.
- **Same-origin**: o componente acessa o `document` interno do iframe (para o F5). Ele assume URLs do próprio sistema — com uma URL externa o navegador bloqueia esse acesso e só o comportamento do F5 deixa de funcionar.
