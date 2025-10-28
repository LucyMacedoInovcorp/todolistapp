<?php

/*
    TESTES DESTA PÁGINA:
    • Edição de tarefas:
        ○ Se pode editar o título, descrição, data de vencimento e prioridade de uma tarefa existente.
*/

use App\Models\Tarefa;

// ========== TESTES DE EDIÇÃO INDIVIDUAL DE CAMPOS ==========

test('usuário pode editar título de uma tarefa existente', function () {
    // Arrange - Criar tarefa original
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Título Original',
        'descricao' => 'Descrição original',
        'prioridade' => 'media',
        'concluida' => false
    ]);

    // Act - Editar apenas o título
    $dadosEdicao = [
        'titulo' => 'Título Editado'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Assert - Verificar se editou corretamente
    $response->assertStatus(200)
        ->assertJson([
            'titulo' => 'Título Editado',
            'descricao' => 'Descrição original', // Não deve mudar
            'prioridade' => 'media' // Não deve mudar
        ]);

    // Verificar no banco de dados
    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->titulo)->toBe('Título Editado');
    expect($tarefaAtualizada->descricao)->toBe('Descrição original');
    expect($tarefaAtualizada->prioridade)->toBe('media');
});

test('usuário pode editar descrição de uma tarefa existente', function () {
    // Arrange
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Título Fixo',
        'descricao' => 'Descrição original',
        'prioridade' => 'alta'
    ]);

    // Act - Editar apenas a descrição
    $dadosEdicao = [
        'descricao' => 'Nova descrição muito detalhada'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'titulo' => 'Título Fixo', // Não deve mudar
            'descricao' => 'Nova descrição muito detalhada',
            'prioridade' => 'alta' // Não deve mudar
        ]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->descricao)->toBe('Nova descrição muito detalhada');
});

test('usuário pode editar data de vencimento de uma tarefa existente', function () {
    // Arrange
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa com prazo',
        'descricao' => 'Descrição fixa',
        'data_vencimento' => '2025-12-01',
        'prioridade' => 'media'
    ]);

    // Act - Editar apenas a data de vencimento
    $dadosEdicao = [
        'dataVencimento' => '2025-12-31'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Assert
    $response->assertStatus(200);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->data_vencimento->format('Y-m-d'))->toBe('2025-12-31');
    expect($tarefaAtualizada->titulo)->toBe('Tarefa com prazo'); // Não mudou
    expect($tarefaAtualizada->prioridade)->toBe('media'); // Não mudou
});

test('usuário pode editar prioridade de uma tarefa existente', function () {
    // Arrange
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa importante',
        'descricao' => 'Descrição fixa',
        'prioridade' => 'baixa'
    ]);

    // Act - Mudar prioridade de baixa para alta
    $dadosEdicao = [
        'prioridade' => 'alta'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'titulo' => 'Tarefa importante', // Não mudou
            'prioridade' => 'alta' // Mudou
        ]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->prioridade)->toBe('alta');
});

// ========== TESTES DE EDIÇÃO COMPLETA ==========

test('usuário pode editar todos os campos de uma tarefa ao mesmo tempo', function () {
    // Arrange - Criar tarefa original
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Título Original',
        'descricao' => 'Descrição original',
        'data_vencimento' => '2025-11-01',
        'prioridade' => 'baixa',
        'concluida' => false
    ]);

    // Act - Editar TODOS os campos
    $dadosEdicao = [
        'titulo' => 'Título Completamente Novo',
        'descricao' => 'Nova descrição muito detalhada e completa',
        'dataVencimento' => '2025-12-25',
        'prioridade' => 'alta'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'titulo' => 'Título Completamente Novo',
            'descricao' => 'Nova descrição muito detalhada e completa',
            'prioridade' => 'alta',
            'concluida' => false // Não foi alterado
        ]);

    // Verificar no banco
    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->titulo)->toBe('Título Completamente Novo');
    expect($tarefaAtualizada->descricao)->toBe('Nova descrição muito detalhada e completa');
    expect($tarefaAtualizada->data_vencimento->format('Y-m-d'))->toBe('2025-12-25');
    expect($tarefaAtualizada->prioridade)->toBe('alta');
    expect($tarefaAtualizada->concluida)->toBeFalse();
});

// ========== TESTES DE VALIDAÇÃO ==========

test('título é obrigatório ao editar tarefa', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Título Original',
        'descricao' => 'Descrição'
    ]);

    // Tentar editar removendo o título
    $dadosEdicao = [
        'titulo' => '', // Título vazio
        'descricao' => 'Nova descrição'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Deve retornar erro de validação
    $response->assertStatus(422)
        ->assertJsonValidationErrors('titulo');

    // Verificar que não foi alterado
    $tarefaOriginal = Tarefa::find($tarefa->id);
    expect($tarefaOriginal->titulo)->toBe('Título Original');
});

test('prioridade deve ser válida ao editar', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa Teste',
        'prioridade' => 'media'
    ]);

    // Tentar editar com prioridade inválida
    $dadosEdicao = [
        'prioridade' => 'super_urgente' // Prioridade inválida
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('prioridade');

    // Verificar que não foi alterado
    $tarefaOriginal = Tarefa::find($tarefa->id);
    expect($tarefaOriginal->prioridade)->toBe('media');
});

test('data de vencimento deve ser válida ao editar', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa com Data',
        'data_vencimento' => '2025-12-01'
    ]);

    // Tentar editar com data inválida
    $dadosEdicao = [
        'dataVencimento' => 'data-invalida'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('dataVencimento');
});

// ========== TESTES DE CASOS ESPECIAIS ==========

test('pode remover descrição de uma tarefa (tornar null)', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa com Descrição',
        'descricao' => 'Esta tarefa tinha descrição'
    ]);

    // Remover a descrição
    $dadosEdicao = [
        'descricao' => null
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    $response->assertStatus(200)
        ->assertJson([
            'descricao' => null
        ]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->descricao)->toBeNull();
});

test('pode remover data de vencimento de uma tarefa', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa com Prazo',
        'data_vencimento' => '2025-12-01'
    ]);

    // Remover a data de vencimento
    $dadosEdicao = [
        'dataVencimento' => null
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    $response->assertStatus(200);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->data_vencimento)->toBeNull();
});

test('retorna erro 404 ao tentar editar tarefa inexistente', function () {
    $dadosEdicao = [
        'titulo' => 'Novo título'
    ];

    $response = $this->putJson('/api/tarefas/99999', $dadosEdicao);

    $response->assertStatus(404);
});

test('edição preserva campos não enviados', function () {
    // Criar tarefa com todos os campos preenchidos
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Título Original',
        'descricao' => 'Descrição Original',
        'data_vencimento' => '2025-11-15',
        'prioridade' => 'media',
        'concluida' => true
    ]);

    // Editar apenas o título
    $dadosEdicao = [
        'titulo' => 'Só o Título Mudou'
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    $response->assertStatus(200);

    // Verificar que outros campos foram preservados
    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->titulo)->toBe('Só o Título Mudou');
    expect($tarefaAtualizada->descricao)->toBe('Descrição Original'); // Preservado
    expect($tarefaAtualizada->data_vencimento->format('Y-m-d'))->toBe('2025-11-15'); // Preservado
    expect($tarefaAtualizada->prioridade)->toBe('media'); // Preservado
    expect($tarefaAtualizada->concluida)->toBeTrue(); // Preservado
});

