<?php

/*
    TESTES DESTA PÁGINA:
    • Conclusão de tarefas:
        ○ Se pode marcar tarefas como concluídas.
*/

use App\Models\Tarefa;

// ========== TESTES DE MARCAR COMO CONCLUÍDA ==========

test('usuário pode marcar tarefa pendente como concluída', function () {
    // Arrange - Criar tarefa pendente
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa a ser concluída',
        'descricao' => 'Esta tarefa precisa ser finalizada',
        'concluida' => false,
        'prioridade' => 'alta'
    ]);

    // Verificar estado inicial
    expect($tarefa->concluida)->toBeFalse();

    // Act - Marcar como concluída usando o endpoint toggle
    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    // Assert - Verificar resposta
    $response->assertStatus(200)
        ->assertJson([
            'id' => $tarefa->id,
            'titulo' => 'Tarefa a ser concluída',
            'concluida' => true
        ]);

    // Verificar no banco de dados
    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->concluida)->toBeTrue();
});

test('usuário pode marcar tarefa como concluída usando PUT', function () {
    // Arrange - Criar tarefa pendente
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa via PUT',
        'concluida' => false
    ]);

    // Act - Marcar como concluída usando PUT completo
    $dadosEdicao = [
        'concluida' => true
    ];

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", $dadosEdicao);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'titulo' => 'Tarefa via PUT',
            'concluida' => true
        ]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->concluida)->toBeTrue();
});

// ========== TESTES DE TOGGLE (ALTERNAR ESTADO) ==========

test('toggle alterna estado de pendente para concluída', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa para toggle',
        'concluida' => false
    ]);

    // Primeira chamada - pendente para concluída
    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200)
        ->assertJson(['concluida' => true]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->concluida)->toBeTrue();
});

test('toggle alterna estado de concluída para pendente', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa já concluída',
        'concluida' => true
    ]);

    // Primeira chamada - concluída para pendente
    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200)
        ->assertJson(['concluida' => false]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->concluida)->toBeFalse();
});

test('múltiplos toggles alternam corretamente o estado', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa para múltiplos toggles',
        'concluida' => false
    ]);

    // 1º toggle: false → true
    $response1 = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");
    $response1->assertStatus(200)->assertJson(['concluida' => true]);

    // 2º toggle: true → false
    $response2 = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");
    $response2->assertStatus(200)->assertJson(['concluida' => false]);

    // 3º toggle: false → true
    $response3 = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");
    $response3->assertStatus(200)->assertJson(['concluida' => true]);

    // Verificar estado final no banco
    $tarefaFinal = Tarefa::find($tarefa->id);
    expect($tarefaFinal->concluida)->toBeTrue();
});

// ========== TESTES DE PRESERVAÇÃO DE DADOS ==========

test('marcar como concluída preserva outros campos da tarefa', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa Completa',
        'descricao' => 'Descrição detalhada',
        'data_vencimento' => '2025-12-31',
        'prioridade' => 'alta',
        'concluida' => false
    ]);

    // Marcar como concluída
    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200);

    // Verificar que outros campos foram preservados
    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->titulo)->toBe('Tarefa Completa');
    expect($tarefaAtualizada->descricao)->toBe('Descrição detalhada');
    expect($tarefaAtualizada->data_vencimento->format('Y-m-d'))->toBe('2025-12-31');
    expect($tarefaAtualizada->prioridade)->toBe('alta');
    expect($tarefaAtualizada->concluida)->toBeTrue(); // Só este mudou
});

// ========== TESTES DE CASOS ESPECIAIS ==========

test('pode marcar tarefa sem descrição como concluída', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa sem descrição',
        'descricao' => null,
        'concluida' => false
    ]);

    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200)
        ->assertJson([
            'titulo' => 'Tarefa sem descrição',
            'descricao' => null,
            'concluida' => true
        ]);
});

test('pode marcar tarefa sem data de vencimento como concluída', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa sem prazo',
        'data_vencimento' => null,
        'concluida' => false
    ]);

    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200)
        ->assertJson(['concluida' => true]);

    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->data_vencimento)->toBeNull();
    expect($tarefaAtualizada->concluida)->toBeTrue();
});

test('pode marcar tarefa vencida como concluída', function () {
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Tarefa vencida',
        'data_vencimento' => '2025-01-01', // Data no passado
        'concluida' => false
    ]);

    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200)
        ->assertJson(['concluida' => true]);

    // Tarefa vencida pode ser marcada como concluída
    $tarefaAtualizada = Tarefa::find($tarefa->id);
    expect($tarefaAtualizada->concluida)->toBeTrue();
});

// ========== TESTES DE ERRO ==========

test('retorna erro 404 ao tentar marcar tarefa inexistente como concluída', function () {
    $response = $this->patchJson('/api/tarefas/99999/toggle');

    $response->assertStatus(404);
});

// ========== TESTES DE LISTAGEM COM FILTRO ==========

test('tarefas concluídas aparecem no filtro correto', function () {
    // Criar tarefas mistas
    $tarefaPendente = $this->createTarefaForSession([
        'titulo' => 'Tarefa Pendente',
        'concluida' => false
    ]);

    $tarefaConcluida = $this->createTarefaForSession([
        'titulo' => 'Tarefa Concluída',
        'concluida' => true
    ]);

    // Marcar a pendente como concluída
    $this->patchJson("/api/tarefas/{$tarefaPendente->id}/toggle");

    // Filtrar apenas concluídas
    $response = $this->getJson('/api/tarefas?estado=concluida');

    $response->assertStatus(200)
        ->assertJsonCount(2); // Ambas devem aparecer

    $tarefas = $response->json();
    foreach ($tarefas as $tarefa) {
        expect($tarefa['concluida'])->toBeTrue();
    }
});

test('tarefas recém-marcadas como pendentes aparecem no filtro pendente', function () {
    // Criar tarefa concluída
    $tarefa = $this->createTarefaForSession([
        'titulo' => 'Era concluída',
        'concluida' => true
    ]);

    // Marcar como pendente novamente
    $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    // Filtrar apenas pendentes
    $response = $this->getJson('/api/tarefas?estado=pendente');

    $response->assertStatus(200);
    
    $tarefas = $response->json();
    $encontrada = false;
    foreach ($tarefas as $tarefaResponse) {
        if ($tarefaResponse['id'] === $tarefa->id) {
            expect($tarefaResponse['concluida'])->toBeFalse();
            $encontrada = true;
        }
    }
    
    expect($encontrada)->toBeTrue();
});

                // ========== TESTE DE INTEGRAÇÃO ==========

                test('fluxo completo: criar, marcar como concluída e verificar', function () {
                    // 1. Criar tarefa pendente
                    $dadosTarefa = [
                        'titulo' => 'Tarefa do Fluxo Completo',
                        'descricao' => 'Testar fluxo end-to-end'
    ];

    $responseCriacao = $this->postJson('/api/tarefas', $dadosTarefa);
    $responseCriacao->assertStatus(201);
    
    $tarefaCriada = $responseCriacao->json();
    expect($tarefaCriada['concluida'])->toBeFalse();

    // 2. Marcar como concluída
    $responseConclusao = $this->patchJson("/api/tarefas/{$tarefaCriada['id']}/toggle");
    $responseConclusao->assertStatus(200)
        ->assertJson(['concluida' => true]);

    // 3. Verificar nos detalhes
    $responseDetalhes = $this->getJson("/api/tarefas/{$tarefaCriada['id']}");
    $responseDetalhes->assertStatus(200)
        ->assertJson([
            'titulo' => 'Tarefa do Fluxo Completo',
            'concluida' => true
        ]);

    // 4. Verificar na listagem filtrada
    $responseListagem = $this->getJson('/api/tarefas?estado=concluida');
    $responseListagem->assertStatus(200);
    
    $tarefas = $responseListagem->json();
    $encontrada = collect($tarefas)->contains('id', $tarefaCriada['id']);
    expect($encontrada)->toBeTrue();
});

