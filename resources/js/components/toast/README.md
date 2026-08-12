# Toast

Componente de notificação (toast), empilhável, no canto superior direito da tela. É registrado globalmente em `window.Toast` a partir de `resources/js/app.js`, que carrega antes de qualquer página — **não precisa ser importado novamente** em `resources/js/pages/*.js`.

É dependência do [`RequestHelper`](../../helpers/request/README.md) (usado para reportar erros de requisição), mas pode ser usado sozinho em qualquer lugar.

## Uso

```js
Toast.success('Salvo com sucesso!');
Toast.error('<strong>Falha</strong> ao salvar o registro.');
Toast.warning('Verifique os campos destacados.');
Toast.info('Sua sessão expira em 5 minutos.');

// forma completa, com duração customizada (ms) — 0 = não some sozinho
Toast.show({ type: 'error', message: '<strong>Erro</strong>', duration: 8000 });
```

## Métodos estáticos

| Método | Descrição |
| --- | --- |
| `Toast.show({ type = 'info', message = '', duration = 5000 })` | Método base. Cria e empilha um toast. `duration` em ms; `0` desativa o auto-fechamento (só fecha no X). |
| `Toast.success(message, duration = 5000)` | Atalho para `type: 'success'`. |
| `Toast.error(message, duration = 5000)` | Atalho para `type: 'error'`. |
| `Toast.warning(message, duration = 5000)` | Atalho para `type: 'warning'`. |
| `Toast.info(message, duration = 5000)` | Atalho para `type: 'info'`. |

## Conteúdo é HTML

`message` é inserido via `.html()` (innerHTML), **não é texto puro** — pode (e deve) conter tags, ex: `Toast.error('<strong>Erro 500</strong><br>Tente novamente.')`.

A área da mensagem tem `max-height: 6rem` com `overflow-y: auto`: mensagens longas ganham scroll interno em vez de esticar o toast ou empurrar/cobrir o botão de fechar (que fica numa coluna fixa própria, fora do fluxo da mensagem).

## Tipos e ícones

| Tipo | Cor | Ícone (Font Awesome) |
| --- | --- | --- |
| `success` | `var(--green-500)` | `fa-circle-check` |
| `error` | `var(--red-500)` | `fa-circle-xmark` |
| `warning` | `var(--yellow-500)` | `fa-triangle-exclamation` |
| `info` (padrão) | `var(--primary-500)` | `fa-circle-info` |

## Dependências

Usa classes `fa-solid` do Font Awesome, já carregado globalmente em `app.js`. Não depende de nenhum outro componente do projeto.
