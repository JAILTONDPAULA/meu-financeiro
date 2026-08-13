# Dialog

Modal sobreposto (`fixed`, `z-index: 9`, `100vw x 100dvh`) com um `<form>` centralizado, dividido em `header`, `.body` e `footer`. É registrado globalmente em `window.Dialog` a partir de `resources/js/app.js`, que também chama `Dialog.events.init()` — o `init` liga os fechamentos (X, botão Fechar, clique no fundo e Escape).

O formulário não tem padding: quem espaça são as três faixas internas.

## Uso

```js
// confirmação simples
Dialog.create({
    titulo: 'Excluir movimentação',
    id: 'dialog-excluir',
    body: '<p>Essa ação não pode ser desfeita.</p>',
    submit: 'Excluir',
    tipo: 'error',
});

// o submit é da página, não do componente
$(document).on('submit', '#dialog-excluir', event => {
    event.preventDefault();
    // ...
    Dialog.close('dialog-excluir');
});
```

## Métodos estáticos

| Método | Descrição |
| --- | --- |
| `Dialog.create({ titulo, id, body, submit, tipo })` | Cria o diálogo e devolve o `id` informado. |
| `Dialog.close(id)` | Fecha pelo id do `<form>`, com a animação reversa. |
| `Dialog.closeAll()` | Fecha todos os diálogos abertos. |
| `Dialog.events.init()` | Liga os fechamentos. Chamado uma vez em `app.js`. |

## Parâmetros do `create`

| Parâmetro | Padrão | Descrição |
| --- | --- | --- |
| `titulo` | `''` | Texto do cabeçalho, ao lado do ícone de fechar. Inserido como texto, não como HTML. |
| `id` | — | Vai no `<form>`. É por ele que a página estiliza o corpo e escuta o `submit`. |
| `body` | `''` | HTML injetado dentro de `.body`. |
| `submit` | `false` | Quando for uma string, gera um `<button type="submit">` no rodapé com esse texto. |
| `tipo` | `null` | `'error'` aplica os tons vermelhos no título e no botão de enviar. |

O botão **Fechar** (`type="button"`) existe sempre, independente do `submit`.

## Estilo do corpo é da página

O componente só garante o esqueleto e o padding das faixas. O conteúdo do `.body` é estilizado pela página que chamou, usando o `id` do formulário como âncora:

```sass
#dialog-excluir

    .body

        strong
            color: var(--red-600)
```

## Fechamento

Quatro caminhos, todos ligados no `init`: o **X** do cabeçalho, o botão **Fechar** do rodapé, **clique no fundo escuro** (fora do formulário) e a tecla **Escape** (fecha apenas o diálogo do topo da pilha).

Enquanto houver algum diálogo aberto, o `body` da página fica com `overflow: hidden`; o scroll volta quando o último é fechado.

## Animação

Entrada com `dialog-in` no fundo e `dialog-form-in` no formulário (sobe e escala). O fechamento adiciona `.is-closing` e remove o elemento no `animationend`, então `Dialog.close()` não é instantâneo — o nó sai da árvore ao fim da animação.
