# Preload

Componente de tela de carregamento (preload) fullscreen (`100%` x `100dvh`), usado para cobrir a aplicação enquanto algo é processado (ex: login, requisições, troca de página).

É registrado globalmente em `window.Preload` a partir de `resources/js/app.js`, que carrega antes de qualquer página. Por isso, **não precisa ser importado novamente** nos arquivos de `resources/js/pages/*.js` — basta chamar `Preload.xxx()` diretamente.

## Uso

```js
// exibe o preload
Preload.create('Autenticando...');

// troca a mensagem exibida, sem recriar o elemento
Preload.message('Quase lá...');

// remove o preload (com animação de saída)
Preload.destroy();
```

## Métodos estáticos

| Método | Descrição |
| --- | --- |
| `Preload.create(message = 'Carregando...')` | Cria e insere o preload no `<body>`. Se já existir um preload ativo, apenas atualiza a mensagem (não duplica o elemento). |
| `Preload.message(text)` | Substitui a mensagem exibida no preload atual, sem recriar/remover o elemento. Não faz nada se não houver preload ativo. |
| `Preload.destroy()` | Remove o preload, aplicando uma pequena animação de saída (`fade-out`) antes de tirá-lo do DOM. Não faz nada se não houver preload ativo. |

## Estrutura interna

```html
<div class="preload">
    <section class="background">
        <div></div>
        <div></div>
        <div></div>
    </section>
    <div class="content">
        <div class="header">
            <h1>Meu Financeiro</h1>
            <p>Carregando...</p>
        </div>
        <div class="loading-bar">
            <span></span>
        </div>
    </div>
</div>
```

- `section.background` reaproveita a mesma animação de fundo do layout (já estilizada globalmente em `resources/sass/main.sass`) — por isso o componente não redefine esse estilo.
- `.content .header` (h1 + p) sobe com fade a partir de baixo ao aparecer.
- `.loading-bar` é uma barra de progresso indeterminada (infinita).

## Estilo

O SASS fica em `preload.sass`, na mesma pasta, e é importado diretamente por `preload.js`. Segue a mesma convenção de cascata do restante do projeto: nada de blocos soltos — cada regra (inclusive `@media`) fica aninhada dentro do seletor que ela afeta.
