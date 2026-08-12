# Menu

Barra superior fixa (`sticky; top: 0`) + sidebar (`fixed`) do shell autenticado. O HTML é renderizado no servidor pelo componente Blade [`resources/views/components/menu.blade.php`](../../../views/components/menu.blade.php) (`<x-menu />`); este `menu.js`/`menu.sass` só cuidam do comportamento e do estilo.

É registrado globalmente em `window.Menu` a partir de `resources/js/app.js`, que também chama `Menu.events.init()` logo após `window.$` ser definido — os binds usam delegação de evento em `document`, então funcionam em qualquer página, mesmo que ela não renderize `<x-menu />`.

## Uso

Inclua o componente Blade na página:

```blade
<x-menu />
```

Nenhuma chamada JS é necessária — o toggle do menu hamburguer, o fechar da sidebar (botão de close ou clique no overlay) e o logout já ficam funcionando pelos IDs fixos do markup.

## Dependências

- [`AuthApi.logout()`](../../apis/auth.js) → [`RequestHelper`](../../helpers/request/README.md), que por sua vez depende de [`Preload`](../preload/README.md) e [`Toast`](../toast/README.md) já carregados. O botão de sair (`#btn-logout`) chama `AuthApi.logout()` e redireciona para `/login` quando resolve.
- Ícones `fa-solid` do Font Awesome (já carregado globalmente em `app.js`).
- Rota nomeada `home` (usada no link "Movimentação" da sidebar).
- Fonte `Fredoka` (Google Fonts, carregada em `layout/app.blade.php` junto com Poppins/Roboto Mono) — usada só no título `.brand`.

## Markup esperado (IDs/classes que o JS usa)

| Seletor | Papel |
| --- | --- |
| `#menu-toggle` | Botão hamburguer, à esquerda da barra superior (junto do título) — abre a sidebar. |
| `#sidebar` | Painel lateral (`fixed`, `z-index: 100`). |
| `#sidebar-overlay` | Fundo escurecido atrás da sidebar (`z-index: 90`) — clicar nele fecha. |
| `#sidebar-close` | Botão de fechar dentro do header da sidebar. |
| `#btn-logout` | Botão de sair. Fica **dentro da sidebar** (`.body`, depois das opções de navegação, empurrado para o fim da seção com `margin-top: auto`) — não mais na barra superior. |

O título (`.brand`) é dividido em dois `<span>` (`.a` = "Meu", `.b` = "Financeiro") para permitir duas cores diferentes no logotipo.

Abrir/fechar é só a classe `.is-open` em `#sidebar` e `#sidebar-overlay` (`Menu.open()` / `Menu.close()`), animada via `transform`/`opacity` no SASS.

## Camadas (z-index)

`.menu` = `5` (sticky, fica acima do conteúdo da página mas abaixo de tudo relacionado à sidebar) · `#sidebar-overlay` = `90` · `#sidebar` = `100`.

## Responsivo

Abaixo de `480px`: o e-mail do usuário na barra superior é escondido (mantém só o nome) e a sidebar passa a ocupar `100vw` em vez de `280px`/`85vw`.
