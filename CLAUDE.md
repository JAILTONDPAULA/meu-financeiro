# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Comandos

```bash
composer dev                      # sobe server + pail (logs) + vite juntos, via concurrently
composer setup                    # install, .env, key:generate, npm install, build
php artisan test                  # roda a suíte (sqlite :memory:, ver phpunit.xml)
php artisan test --filter=NomeDoTeste
php artisan test tests/Feature/ExampleTest.php
php artisan migrate
./vendor/bin/pint                 # formatação PHP
```

**Não rode `npm run build` nem `npm run dev` para validar alterações de front-end.** O usuário mantém o Vite em modo dev rodando permanentemente; basta conferir os arquivos gerados.

## Stack

Laravel 12 · PHP 8.2 · PostgreSQL (porta 5433) · Vite 7 · jQuery 4 · SASS (sintaxe indentada). O Tailwind está instalado como plugin do Vite, mas a estilização real é feita em SASS — não introduza classes utilitárias.

Código, comentários, nomes de rota, colunas e textos de UI são em **português**. Mantenha esse padrão.

## Arquitetura back-end

**A API compartilha a sessão do front.** `bootstrap/app.php` prepende `EncryptCookies`, `AddQueuedCookiesToResponse` e `StartSession` ao grupo `api`. Consequências:

- quem faz login em `POST /api/login` fica autenticado nas rotas web, e vice-versa;
- o grupo `api` **não** tem `VerifyCsrfToken`, por isso o `fetch` do front funciona sem token CSRF.

**Separação rígida de responsabilidades entre os arquivos de rota:** `routes/web.php` só devolve views (closures com `view(...)`); toda ação (login, logout, escrita) vive em `routes/api.php` sob controllers em `app/Http/Controllers/Api/`.

**Middleware `authenticated`** (alias de `EnsureUserIsAuthenticated`) responde diferente por contexto: `401` + texto cru para `api/*`, redirect para `/login` no web.

**Toda API delega para um Service.** A regra de negócio vive em `app/Services/<Modelo>Service.php`, injetado no construtor do controller. Ao controller cabe só validar a entrada, chamar o service e formatar a resposta — nunca montar query ou aplicar regra de domínio. **Exceção:** execuções simples, sem regra de negócio, podem resolver direto no controller (ex.: `AuthController::me`, que apenas devolve o usuário da sessão).

**Contrato de erro — texto cru, não JSON.** Os controllers retornam `response('mensagem', 422)` com a mensagem já pronta para exibição, não um envelope JSON. O `RequestHelper` faz `throw` do corpo da resposta e o `Toast.error` exibe essa string direto ao usuário. Ao criar endpoints, siga isso: mensagem em português, legível, no corpo. Controllers envolvem tudo em `try/catch (Throwable)` com `report($e)` e mensagem genérica no `500`.

**Domínio:** `movimentacoes` usa flags `char(1)` com CHECK constraints aplicadas via `DB::statement` cru na migration (`tipo` F/D, `flg_credito` e `recorrente` S/N). `movimentacao_original_id` é auto-referência: a movimentação-mãe tem `parcelas()` apontando para ela.

**Integração com o Claude:** SDK oficial `anthropic-ai/sdk`, credenciais em `config/services.php` (`anthropic.key` / `anthropic.model`, vindas do `.env`). O `InterpretacaoMovimentacaoService` transforma fala em JSON compatível com `POST /api/movimentacoes`. O prompt injeta a lista de categorias do banco para o modelo escolher o `categoria_id` — ao adicionar campos na movimentação, atualize o prompt junto.

**Nomes de tabela em português exigem `$table` explícito.** A pluralização automática do Eloquent segue regras do inglês (`Movimentacao` → `movimentacaos`). Ao criar um model novo, declare `protected $table` sempre que o plural em português divergir — `Categoria` → `categorias` funciona por coincidência, a maioria não vai funcionar.

**Parcelamento no crédito:** `movimentacao_original_id` nunca vem da API — quem preenche é o `MovimentacaoService`. Quando `flg_credito = 'S'`, o campo `parcela` diz quantos registros gerar: o primeiro fica com `movimentacao_original_id` nulo e os demais apontam para o id dele, com `faturamento_ym` avançando um mês por parcela. Tudo dentro de uma transação.

## Arquitetura front-end (`resources/js`)

Quatro camadas com regras distintas:

| Pasta | Formato | Registro |
| --- | --- | --- |
| `components/<nome>/` | `<nome>.js` + `<nome>.sass` + `README.md` | `window.<Nome> = <Nome>` no fim do próprio arquivo |
| `helpers/<nome>/` | igual a component, sem `.sass` | idem |
| `apis/<nome>.js` | arquivo plano, `export default class` | nenhum — importado explicitamente pela page |
| `pages/<nome>.js` | arquivo plano, um por tela | entry point próprio no Vite |

**A auto-registração em `window` mora no arquivo do próprio componente**, nunca em `app.js`. O `app.js` é só uma lista de imports com efeito colateral, e como ele carrega antes de qualquer script de página (ordem das tags `@vite` no layout), as pages usam `Preload`, `Toast`, `Iframe` e `RequestHelper` diretamente, sem import.

**Toda tela nova precisa de três registros**: o arquivo em `resources/js/pages/`, a entrada em `input` no `vite.config.js` e a tag `@vite([...])` dentro de `@section('head')` da blade. Esquecer o `vite.config.js` faz a tela carregar sem JS nenhum. Existe a skill `criar-tela` (`.claude/skills/criar-tela/`) que automatiza os três passos — use-a ao criar telas.

**Padrão de página** — toda page segue esta forma, e o `Preload.destroy()` no `init()` é obrigatório (é o que fecha o loading global disparado por `app.js`):

```js
class Page {
    static events = {
        init() {
            Page.events.dom();
            Preload.destroy();
        },

        dom() {
            $(document).on('click', '.seletor', ...);   // delegação a partir de document
        },
    };
}

$(document).ready(_ => Page.events.init());
```

Componentes e helpers são classes com **membros estáticos apenas** (privados com `#`), nunca instanciadas.

### Convenções de escrita

**HTML em JS por concatenação**, uma linha por tag, indentação espelhando o aninhamento — não template literal multilinha:

```js
const $el = $(
    '<div class="foo">' +
        '<span>bar</span>' +
    '</div>'
);
```

**SASS em cascata**: sintaxe indentada, tudo aninhado dentro do seletor que afeta, **inclusive `@media`**. Só `@keyframes` fica no topo do arquivo. Cores sempre por variável CSS de `_colors.sass` (`var(--primary-500)`, `var(--red-400)`…) — a paleta `--primary-*` é um alias de `--blue-*`.

## Ambiente de desenvolvimento

`vite.config.js` fixa `server.host: '0.0.0.0'` e `server.hmr.host: '192.168.1.5'` para permitir testar pelo celular na LAN — esse IP é o da máquina do dev e precisa bater com `APP_URL`/`VITE_DEV_SERVER_URL` do `.env`.

APIs de navegador que exigem *secure context* (microfone, câmera, geolocalização) **não funcionam** no acesso por IP de LAN sobre HTTP, só em `localhost` ou HTTPS.
