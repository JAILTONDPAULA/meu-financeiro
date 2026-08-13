<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovimentacaoController;
use App\Http\Controllers\Api\MovimentacaoVozController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas de API
|--------------------------------------------------------------------------
|
| Prefixo /api. O grupo carrega a MESMA sessão das rotas web
| (ver bootstrap/app.php), então o login feito aqui autentica o front.
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('authenticated')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    Route::get('/movimentacoes', [MovimentacaoController::class, 'listar'])->name('api.movimentacoes.listar');
    Route::post('/movimentacoes', [MovimentacaoController::class, 'salvar'])->name('api.movimentacoes.salvar');
    Route::delete('/movimentacoes/{id}', [MovimentacaoController::class, 'excluir'])->whereNumber('id')->name('api.movimentacoes.excluir');
    Route::post('/movimentacoes/interpretar', [MovimentacaoVozController::class, 'interpretar'])->name('api.movimentacoes.interpretar');
});
