<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar sessão para testes
        $this->startSession();
        
        // Simular um session_id fixo para testes
        session(['test_session_id' => 'test-session-123']);
    }

    /**
     * Simula uma requisição com sessão específica
     */
    protected function withTestSession($sessionId = 'test-session-123')
    {
        session(['test_session_id' => $sessionId]);
        return $this;
    }

    /**
     * Cria uma tarefa associada à sessão de teste
     */
    protected function createTarefaForSession($attributes = [], $sessionId = 'test-session-123')
    {
        return \App\Models\Tarefa::create(array_merge([
            'titulo' => 'Tarefa de Teste',
            'descricao' => 'Descrição de teste',
            'prioridade' => 'media',
            'concluida' => false,
            'session_id' => $sessionId
        ], $attributes));
    }

    /**
     * Cria uma tarefa associada a um usuário
     */
    protected function createTarefaForUser($user, $attributes = [])
    {
        return \App\Models\Tarefa::create(array_merge([
            'titulo' => 'Tarefa de Teste',
            'descricao' => 'Descrição de teste',
            'prioridade' => 'media',
            'concluida' => false,
            'user_id' => $user->id
        ], $attributes));
    }

    /**
     * Cria um usuário de teste
     */
    protected function createTestUser($attributes = [])
    {
        return \App\Models\User::create(array_merge([
            'name' => 'Usuário Teste',
            'email' => 'teste@example.com',
            'password' => bcrypt('password')
        ], $attributes));
    }
}
