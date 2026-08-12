<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MeuFinanceiro')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Mono:wght@400;500&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js'])

    @yield('head')
</head>
<body>
    @yield('body')
    <section class="background">
        @for ($i = 0; $i < 3; $i++)
            <div></div>
        @endfor
    </section>
</body>
</html>
