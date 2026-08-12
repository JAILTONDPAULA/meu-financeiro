<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas web (telas)
|--------------------------------------------------------------------------
|
| Só devolvem views. Login, logout e demais ações ficam em routes/api.php;
| aqui apenas o middleware 'authenticated' protege as telas e redireciona
| para /login quando a sessão não está autenticada.
|
*/
Route::get('/login', fn() => view('pages.login'));
Route::middleware('authenticated')->group(function () {
    // Tela de teste: sem sessão autenticada, cai em /login.
    Route::get('/', fn() => view('pages.home'))->name('home');
    Route::get('/movimentacoes', fn() => view('pages.movimentacoes'))->name('movimentacoes');
});
