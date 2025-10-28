<?php

/*
    TESTES DESTA PÁGINA:
    • Listagem de tarefas:
        ○ Exibir todas as tarefas numa lista, com opção de filtrar por estado (pendente, concluída, todas), prioridade e data de vencimento.
        ○ Mostrar detalhes da tarefa ao clicar num item da lista.
*/

use App\Models\Tarefa;

// ========== TESTES DE LISTAGEM BÁSICA ==========

test('pode listar todas as tarefas', function () {
    // Arrange - Criar algumas tarefas de teste para a sessão atual
    $this->createTarefaForSession([
        'titulo' => 'Tarefa 1',
        'descricao' => 'Primeira tarefa',
        'concluida' => false,
        'prioridade' => 'alta'
    ]);

    $this->createTarefaForSession([
        'titulo' => 'Tarefa 2',
        'descricao' => 'Segunda tarefa',
        'concluida' => true,
        'prioridade' => 'media'
    ]);

    $this->createTarefaForSession([
        'titulo' => 'Tarefa 3',
        'descricao' => 'Terceira tarefa',
        'concluida' => false,
        'prioridade' => 'baixa'
    ]);

    // Act - Fazer requisição para listar tarefas
    $response = $this->getJson('/api/tarefas');

    // Assert - Verificar resposta
    $response->assertStatus(200)
        ->assertJsonCount(3) // Deve retornar 3 tarefas
        ->assertJsonStructure([
            '*' => [
                'id',
                'titulo',
                'descricao',
                'concluida',
                'prioridade',
                'data_vencimento',
                'created_at',
                'updated_at'
            ]
        ]);

    // Verificar se todas as tarefas estão presentes (independente da ordem)
    $tarefas = $response->json();
    $titulos = collect($tarefas)->pluck('titulo')->toArray();
    
    expect($titulos)->toContain('Tarefa 1');
    expect($titulos)->toContain('Tarefa 2');
    expect($titulos)->toContain('Tarefa 3');
    expect(count($tarefas))->toBe(3);
});

test('retorna lista vazia quando não há tarefas', function () {
    $response = $this->getJson('/api/tarefas');

    $response->assertStatus(200)
        ->assertJsonCount(0);
});

// ========== TESTES DE FILTRO POR ESTADO ==========

test('pode filtrar tarefas pendentes', function () {
    // Criar tarefas com diferentes estados
    $this->createTarefaForSession(['titulo' => 'Tarefa Pendente 1', 'concluida' => false]);
    $this->createTarefaForSession(['titulo' => 'Tarefa Pendente 2', 'concluida' => false]);
    $this->createTarefaForSession(['titulo' => 'Tarefa Concluída 1', 'concluida' => true]);
    $this->createTarefaForSession(['titulo' => 'Tarefa Concluída 2', 'concluida' => true]);

    // Filtrar apenas pendentes
    $response = $this->getJson('/api/tarefas?estado=pendente');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['concluida'])->toBeFalse();
    }
});

test('pode filtrar tarefas concluídas', function () {
    // Criar tarefas com diferentes estados
    $this->createTarefaForSession(['titulo' => 'Tarefa Pendente 1', 'concluida' => false]);
    $this->createTarefaForSession(['titulo' => 'Tarefa Pendente 2', 'concluida' => false]);
    $this->createTarefaForSession(['titulo' => 'Tarefa Concluída 1', 'concluida' => true]);
    $this->createTarefaForSession(['titulo' => 'Tarefa Concluída 2', 'concluida' => true]);

    // Filtrar apenas concluídas
    $response = $this->getJson('/api/tarefas?estado=concluida');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['concluida'])->toBeTrue();
    }
});

test('pode filtrar todas as tarefas explicitamente', function () {
    // Criar tarefas mistas
    $this->createTarefaForSession(['titulo' => 'Tarefa 1', 'concluida' => false]);
    $this->createTarefaForSession(['titulo' => 'Tarefa 2', 'concluida' => true]);
    $this->createTarefaForSession(['titulo' => 'Tarefa 3', 'concluida' => false]);

    // Filtrar explicitamente por "todas"
    $response = $this->getJson('/api/tarefas?estado=todas');

    $response->assertStatus(200)
        ->assertJsonCount(3);
});

// ========== TESTES DE FILTRO POR PRIORIDADE ==========

test('pode filtrar tarefas por prioridade alta', function () {
    // Criar tarefas com diferentes prioridades
    $this->createTarefaForSession(['titulo' => 'Tarefa Alta 1', 'prioridade' => 'alta']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Alta 2', 'prioridade' => 'alta']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Média', 'prioridade' => 'media']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Baixa', 'prioridade' => 'baixa']);

    $response = $this->getJson('/api/tarefas?prioridade=alta');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['prioridade'])->toBe('alta');
    }
});

test('pode filtrar tarefas por prioridade média', function () {
    $this->createTarefaForSession(['titulo' => 'Tarefa Alta', 'prioridade' => 'alta']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Média 1', 'prioridade' => 'media']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Média 2', 'prioridade' => 'media']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Baixa', 'prioridade' => 'baixa']);

    $response = $this->getJson('/api/tarefas?prioridade=media');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['prioridade'])->toBe('media');
    }
});

test('pode filtrar tarefas por prioridade baixa', function () {
    $this->createTarefaForSession(['titulo' => 'Tarefa Alta', 'prioridade' => 'alta']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Média', 'prioridade' => 'media']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Baixa 1', 'prioridade' => 'baixa']);
    $this->createTarefaForSession(['titulo' => 'Tarefa Baixa 2', 'prioridade' => 'baixa']);

    $response = $this->getJson('/api/tarefas?prioridade=baixa');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['prioridade'])->toBe('baixa');
    }
});

// ========== TESTES DE FILTRO POR DATA DE VENCIMENTO ==========

test('pode filtrar tarefas por data de vencimento específica', function () {
    // Criar tarefas com diferentes datas
    $dataEspecifica = now()->addDays(2)->format('Y-m-d');
    $this->createTarefaForSession([
        'titulo' => 'Tarefa Hoje 1',
        'data_vencimento' => $dataEspecifica
    ]);
    $this->createTarefaForSession([
        'titulo' => 'Tarefa Hoje 2',
        'data_vencimento' => $dataEspecifica
    ]);
    $this->createTarefaForSession([
        'titulo' => 'Tarefa Amanhã',
        'data_vencimento' => now()->addDays(3)->format('Y-m-d')
    ]);

    $response = $this->getJson('/api/tarefas?data_vencimento=' . $dataEspecifica);

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['data_vencimento'])->toContain($dataEspecifica);
    }
});

test('pode filtrar tarefas vencidas', function () {
    // Criar tarefas com datas passadas e futuras
    $this->createTarefaForSession([
        'titulo' => 'Tarefa Vencida 1',
        'data_vencimento' => now()->subDays(5)->format('Y-m-d') // 5 dias atrás
    ]);
    $this->createTarefaForSession([
        'titulo' => 'Tarefa Vencida 2', 
        'data_vencimento' => now()->subDays(3)->format('Y-m-d') // 3 dias atrás
    ]);
    $this->createTarefaForSession([
        'titulo' => 'Tarefa Futura',
        'data_vencimento' => now()->addDays(5)->format('Y-m-d') // 5 dias no futuro
    ]);

    $response = $this->getJson('/api/tarefas?vencidas=true');

    $response->assertStatus(200)
        ->assertJsonCount(2);
});

// ========== TESTES DE FILTROS COMBINADOS ==========

test('pode combinar filtros de estado e prioridade', function () {
    // Criar tarefas variadas
    $this->createTarefaForSession(['titulo' => 'Target', 'concluida' => false, 'prioridade' => 'alta']);
    $this->createTarefaForSession(['titulo' => 'Não Target 1', 'concluida' => true, 'prioridade' => 'alta']);
    $this->createTarefaForSession(['titulo' => 'Não Target 2', 'concluida' => false, 'prioridade' => 'media']);
    $this->createTarefaForSession(['titulo' => 'Target 2', 'concluida' => false, 'prioridade' => 'alta']);

    $response = $this->getJson('/api/tarefas?estado=pendente&prioridade=alta');

    $response->assertStatus(200)
        ->assertJsonCount(2);

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['concluida'])->toBeFalse();
        expect($tarefa['prioridade'])->toBe('alta');
    }
});

// ========== TESTES DE DETALHES DA TAREFA ==========

test('pode visualizar detalhes de uma tarefa específica', function () {
    // Criar uma tarefa completa
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa Detalhada',
        'descricao' => 'Esta é uma descrição completa da tarefa',
        'concluida' => false,
        'prioridade' => 'alta',
        'data_vencimento' => '2025-12-31'
    ]);

    // Buscar detalhes da tarefa
    $response = $this->getJson("/api/tarefas/{$tarefa->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id' => $tarefa->id,
            'titulo' => 'Tarefa Detalhada',
            'descricao' => 'Esta é uma descrição completa da tarefa',
            'concluida' => false,
            'prioridade' => 'alta'
        ])
        ->assertJsonStructure([
            'id',
            'titulo',
            'descricao',
            'concluida',
            'prioridade',
            'data_vencimento',
            'created_at',
            'updated_at'
        ]);

    // Verificar que a data de vencimento está presente
    $tarefaResponse = $response->json();
    expect($tarefaResponse['data_vencimento'])->toContain('2025-12-31');
});

test('retorna erro 404 para tarefa inexistente', function () {
    $response = $this->getJson('/api/tarefas/99999');

    $response->assertStatus(404);
});

test('retorna todos os campos necessários nos detalhes da tarefa', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Teste Campos',
        'descricao' => 'Descrição teste',
        'concluida' => true,
        'prioridade' => 'media',
        'data_vencimento' => '2025-11-15'
    ]);

    $response = $this->getJson("/api/tarefas/{$tarefa->id}");

    $response->assertStatus(200);

    $tarefaDetalhes = $response->json();

    // Verificar que todos os campos essenciais estão presentes
    expect($tarefaDetalhes)->toHaveKeys([
        'id',
        'titulo',
        'descricao',
        'concluida',
        'prioridade',
        'data_vencimento',
        'created_at',
        'updated_at'
    ]);

    // Verificar tipos de dados
    expect($tarefaDetalhes['id'])->toBeInt();
    expect($tarefaDetalhes['titulo'])->toBeString();
    expect($tarefaDetalhes['concluida'])->toBeBool();
    expect($tarefaDetalhes['prioridade'])->toBeString();
});

