<?php

use App\Http\Controllers\Api\AuthController;
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
});
