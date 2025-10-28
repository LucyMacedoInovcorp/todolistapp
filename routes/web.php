<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Rota para a página inicial - web
Route::get('/', function () {
    return view('home');
})->name('home');

// Rotas de autenticação (páginas web)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');


