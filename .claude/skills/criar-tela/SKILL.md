---
name: criar-tela
description: Use this skill when the user asks to create a new page/screen ("tela") in this Laravel project. Scaffolds the sass, js and blade view for a page following the project's fixed pattern (based on the home screen), registers the page's JS entry point in vite.config.js, and registers the route in routes/web.php.
---

# Criar tela

Gera os 3 arquivos de uma nova tela (sass, js, blade) seguindo o padrão fixo do
projeto — baseado na tela `home` — registra o entry point da tela no
`vite.config.js` e registra a rota correspondente em `routes/web.php`.

## Entrada

Pergunte só o necessário, nessa ordem:

1. **Nome/slug da tela**, em kebab-case (pode incluir subpasta, ex:
   `financeiro/extrato`). Se o usuário já informou no pedido, não pergunte de
   novo. Derive um título em Title Case a partir do slug para usar em
   `@section('title', ...)` e no `<h1>` (ex: `extrato-bancario` ->
   `Extrato Bancario`), a menos que o usuário informe um título diferente.

2. **Rota personalizada?** Pergunte se o usuário quer um path customizado para
   a rota. Se não, use o padrão `/{slug}` (ex: slug `financeiro/extrato` ->
   `/financeiro/extrato`). O nome da rota (`->name(...)`) é sempre derivado do
   slug trocando `/` por `.` (ex: `financeiro/extrato` ->
   `financeiro.extrato`).

3. **Rota de volta.** Pergunte para qual rota o botão de voltar (seta no
   topo da tela) deve levar. Padrão: `home`.

Antes de criar qualquer arquivo, verifique se algum dos 3 arquivos já existe
para esse slug. Se existir, avise o usuário e peça confirmação antes de
sobrescrever.

## Arquivos a gerar

Substitua `{slug}` pelo nome/slug informado, `{Titulo}` pelo título e
`{rota-de-volta}` pelo nome da rota de volta (padrão `home`).

### 1. `resources/sass/pages/{slug}.sass`

Só a base compartilhada com todas as telas (o container de página e a seção
de título com o botão de voltar) — igual à tela `home`. O resto do conteúdo
específico da tela o desenvolvedor adiciona depois, nesse mesmo arquivo.

```sass
.containerpage
    padding: 1.5rem

    @media (max-width: 480px)
        padding: .8rem

    .titulo
        display: flex
        align-items: center
        justify-content: flex-start
        gap: .5rem

        .icon
            display: flex
            justify-content: center
            align-items: center
            flex-shrink: 0
            width: 30px
            height: 30px
            border-radius: 12px
            background: linear-gradient(135deg, var(--primary-400), var(--primary-700))
            color: white
            font-size: 1rem
            text-decoration: none
            cursor: pointer

        h1
            font-size: 1.25rem
            font-weight: 700
            color: var(--primary-900)
            font-family: 'Poppins', sans-serif
```

### 2. `resources/js/pages/{slug}.js`

Use exatamente este padrão, sem adicionar nada além disso:

```js
import '../../sass/pages/{slug}.sass';

class Page {
    static events = {
        init() {
            Page.events.dom();
            Preload.destroy();
        },

        dom() {
            //
        },
    };
}

$(document).ready(_ => Page.events.init());
```

`Preload.destroy()` é obrigatório em `init()` — é o que fecha a tela de
carregamento global assim que a tela termina de montar (`Preload.create()` já
roda automaticamente antes de qualquer página, em `app.js`).

Ajuste apenas o caminho do import do sass conforme a profundidade de `{slug}`
(quantidade de `../`) caso o slug tenha subpasta.

### 3. `resources/views/pages/{slug}.blade.php`

Extende `layout.app`, define o título, injeta o `@vite` da tela no head,
inclui o menu (`<x-menu />`) e a seção de título com o botão de voltar no
lugar do ícone (uma `<a>`, não um `<div>` — leva para `{rota-de-volta}`):

```blade
@extends('layout.app')

@section('title', '{Titulo}')

@section('head')
    @vite(['resources/js/pages/{slug}.js'])
@endsection

@section('body')
<x-menu />
<section class="containerpage">
    <section class="titulo">
        <a href="{{ route('{rota-de-volta}') }}" class="icon">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1>{Titulo}</h1>
    </section>
</section>
@endsection
```

## Registrar no Vite

Edite `vite.config.js` e adicione `'resources/js/pages/{slug}.js'` ao array
`input` do plugin `laravel(...)`, ao lado das entradas já existentes. Não
remova as entradas existentes.

## Registrar a rota

Edite `routes/web.php` e adicione uma rota GET para a tela, dentro do grupo
`Route::middleware('authenticated')->group(...)` (a menos que o usuário peça
uma tela pública), seguindo o padrão já usado no arquivo (closure retornando a
view), sem remover as rotas existentes:

```php
Route::get('{path}', function () {
    return view('pages.{slug-com-pontos}');
})->name('{nome-da-rota}');
```

Onde `{slug-com-pontos}` é o slug com `/` substituído por `.` (ex:
`financeiro/extrato` -> `financeiro.extrato`), igual ao nome da rota.

## Depois de gerar

Não rode `npm run build`/`npm run dev` para validar — o usuário já mantém o
Vite em modo dev rodando. Só confirme visualmente os arquivos gerados.
