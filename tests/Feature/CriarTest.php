<?php

/*
    TESTES DESTA PÁGINA:
	• Criação de tarefas:
		○ Permitir que o utilizador adicione novas tarefas com título e descrição (opcional).
Opção para definir data de vencimento e prioridade (alta, média, baixa).
*/

use App\Models\Tarefa;

// Teste para verificar se o usuário pode criar uma nova tarefa
test('usuário pode adicionar nova tarefa com título e descrição', function () {
    // Arrange (Preparar) - Dados para criar a tarefa
    $dadosTarefa = [
        'titulo' => 'Minha primeira tarefa',
        'descricao' => 'Esta é uma descrição detalhada da tarefa'
    ];

    // Act (Agir) - Fazer a requisição POST para criar a tarefa
    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    // Assert (Verificar) - Confirmar que tudo funcionou como esperado
    $response->assertStatus(201) // Status 201 = Created
        ->assertJson([
            'titulo' => 'Minha primeira tarefa',
            'descricao' => 'Esta é uma descrição detalhada da tarefa',
            'concluida' => false,
            'prioridade' => 'media'
        ]);

    // Verificar se a tarefa foi realmente salva no banco de dados
    $this->assertDatabaseHas('tarefas', [
        'titulo' => 'Minha primeira tarefa',
        'descricao' => 'Esta é uma descrição detalhada da tarefa',
        'concluida' => false
    ]);

    // Verificar se existe exatamente 1 tarefa no banco
    expect(Tarefa::count())->toBe(1);

    // Verificar se a tarefa criada tem os dados corretos
    $tarefa = Tarefa::first();
    expect($tarefa->titulo)->toBe('Minha primeira tarefa');
    expect($tarefa->descricao)->toBe('Esta é uma descrição detalhada da tarefa');
    expect($tarefa->concluida)->toBeFalse();
    expect($tarefa->prioridade)->toBe('media');
});

// Teste para verificar validação de campos obrigatórios
test('título é obrigatório para criar tarefa', function () {
    // Tentando criar tarefa sem título
    $dadosTarefa = [
        'descricao' => 'Tarefa sem título'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    // Deve retornar erro 422 (Unprocessable Entity)
    $response->assertStatus(422)
        ->assertJsonValidationErrors('titulo');

    // Verificar que nenhuma tarefa foi criada
    expect(Tarefa::count())->toBe(0);
});

// Teste para verificar que descrição é opcional
test('pode criar tarefa apenas com título', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa só com título'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    $response->assertStatus(201)
        ->assertJson([
            'titulo' => 'Tarefa só com título',
            'descricao' => null,
            'concluida' => false
        ]);

    // Verificar no banco de dados
    $this->assertDatabaseHas('tarefas', [
        'titulo' => 'Tarefa só com título',
        'descricao' => null
    ]);
});

// Teste para verificar data de vencimento
test('usuário pode adicionar tarefa com data de vencimento', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa com vencimento',
        'descricao' => 'Esta tarefa tem prazo',
        'dataVencimento' => '2025-12-31'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    $response->assertStatus(201)
        ->assertJson([
            'titulo' => 'Tarefa com vencimento',
            'descricao' => 'Esta tarefa tem prazo'
        ]);

    // Verificar no banco
    $this->assertDatabaseHas('tarefas', [
        'titulo' => 'Tarefa com vencimento',
        'data_vencimento' => '2025-12-31'
    ]);

    // Verificar usando expect
    $tarefa = Tarefa::first();
    expect($tarefa->data_vencimento->format('Y-m-d'))->toBe('2025-12-31');
});

// Teste para verificar prioridades
test('usuário pode definir prioridade alta para tarefa', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa urgente',
        'prioridade' => 'alta'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    $response->assertStatus(201)
        ->assertJson([
            'titulo' => 'Tarefa urgente',
            'prioridade' => 'alta'
        ]);

    // Verificar no banco
    $tarefa = Tarefa::first();
    expect($tarefa->prioridade)->toBe('alta');
});

test('usuário pode definir prioridade baixa para tarefa', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa não urgente',
        'prioridade' => 'baixa'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    $response->assertStatus(201);
    
    $tarefa = Tarefa::first();
    expect($tarefa->prioridade)->toBe('baixa');
});

test('prioridade padrão é média quando não especificada', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa sem prioridade definida'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    $response->assertStatus(201)
        ->assertJson([
            'prioridade' => 'media'
        ]);

    $tarefa = Tarefa::first();
    expect($tarefa->prioridade)->toBe('media');
});

test('valida prioridades válidas apenas', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa com prioridade inválida',
        'prioridade' => 'urgentissima' // Prioridade inválida
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    // Deve retornar erro de validação
    $response->assertStatus(422)
        ->assertJsonValidationErrors('prioridade');

    // Verificar que nada foi salvo
    expect(Tarefa::count())->toBe(0);
});

test('pode criar tarefa completa com todos os campos', function () {
    $dadosTarefa = [
        'titulo' => 'Tarefa completa',
        'descricao' => 'Descrição detalhada da tarefa',
        'dataVencimento' => '2025-11-30',
        'prioridade' => 'alta'
    ];

    $response = $this->postJson('/api/tarefas', $dadosTarefa);

    $response->assertStatus(201)
        ->assertJson([
            'titulo' => 'Tarefa completa',
            'descricao' => 'Descrição detalhada da tarefa',
            'prioridade' => 'alta',
            'concluida' => false
        ]);

    // Verificar no banco usando múltiplas verificações
    $tarefa = Tarefa::first();
    expect($tarefa->titulo)->toBe('Tarefa completa');
    expect($tarefa->descricao)->toBe('Descrição detalhada da tarefa');
    expect($tarefa->data_vencimento->format('Y-m-d'))->toBe('2025-11-30');
    expect($tarefa->prioridade)->toBe('alta');
    expect($tarefa->concluida)->toBeFalse();
});
