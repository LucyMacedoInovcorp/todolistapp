<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\AuthController;

// Rotas de autenticação
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas que funcionam tanto autenticadas quanto por sessão
Route::middleware('web')->group(function () {
    Route::get('/tarefas', [TarefaController::class, 'index']);
    Route::post('/tarefas', [TarefaController::class, 'store']);
    Route::get('/tarefas/{tarefa}', [TarefaController::class, 'show']);
    Route::put('/tarefas/{tarefa}', [TarefaController::class, 'update']);
    Route::delete('/tarefas/{tarefa}', [TarefaController::class, 'destroy']);
    Route::patch('/tarefas/{tarefa}/toggle', [TarefaController::class, 'toggleComplete']);
});

// Rotas protegidas (apenas usuários autenticados)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});