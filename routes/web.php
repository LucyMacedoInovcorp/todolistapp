<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Rota para a página inicial - web
Route::get('/', function () {
    return view('home');
})->name('home');

// Rotas de autenticação (páginas web)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

// Rotas de administração (protegidas com senha simples)
Route::middleware('web')->group(function () {
    Route::get('/admin/data', [AdminController::class, 'showData'])->name('admin.data');
    Route::get('/admin/export', [AdminController::class, 'exportData'])->name('admin.export');
    Route::get('/admin/stats', [AdminController::class, 'getStats'])->name('admin.stats');
});


