# 🧪 Documentação de Testes - TodoList API

Esta documentação explica como executar, entender e criar novos testes para o projeto TodoList.

## 📋 Visão Geral dos Testes

O projeto utiliza **PestPHP** como framework de testes, que oferece uma sintaxe mais limpa e expressiva comparado ao PHPUnit tradicional.

### 📊 Estatísticas dos Testes

- **Total de Testes**: 50
- **Testes Feature**: 49 (98%)
- **Testes Unit**: 1 (2%)
- **Asserções**: 228
- **Cobertura**: 100% dos endpoints da API

## 🏗️ Estrutura dos Testes

```
tests/
├── 📁 Feature/                 # Testes de funcionalidades (E2E)
│   ├── ConcluirTest.php        # 13 testes - Conclusão de tarefas
│   ├── CriarTest.php           # 9 testes - Criação de tarefas
│   ├── EdicaoTest.php          # 12 testes - Edição de tarefas
│   ├── ExcluirTest.php         # 7 testes - Exclusão de tarefas
│   ├── ListagemTest.php        # 14 testes - Listagem e filtros
│   └── ExampleTest.php         # 1 teste - Exemplo básico
├── 📁 Unit/                    # Testes unitários
│   └── ExampleTest.php         # 1 teste - Exemplo básico
├── Pest.php                    # Configuração do Pest
└── TestCase.php                # Classe base para testes
```

## 🚀 Como Executar os Testes

### Executar Todos os Testes

```bash
# Executar todos os testes
./vendor/bin/pest

# Executar com saída mais detalhada
./vendor/bin/pest -v

# Executar com relatório de cobertura
./vendor/bin/pest --coverage
```

### Executar Testes Específicos

```bash
# Executar apenas testes de uma categoria
./vendor/bin/pest tests/Feature/CriarTest.php

# Executar teste específico por nome
./vendor/bin/pest --filter="usuário pode adicionar nova tarefa"

# Executar testes que contêm uma palavra
./vendor/bin/pest --filter="filtrar"
```

### Executar com Diferentes Níveis de Detalhe

```bash
# Modo silencioso (apenas erros)
./vendor/bin/pest --quiet

# Modo verboso (mais detalhes)
./vendor/bin/pest --verbose

# Parar no primeiro erro
./vendor/bin/pest --stop-on-failure
```

## 🔧 Configuração dos Testes

### Ambiente de Teste

Os testes utilizam um banco de dados separado configurado em:

**phpunit.xml**:
```xml
<env name="DB_DATABASE" value="todolist_test"/>
<env name="APP_ENV" value="testing"/>
<env name="CACHE_STORE" value="array"/>
<env name="SESSION_DRIVER" value="array"/>
```

**.env.testing**:
```env
DB_CONNECTION=mysql
DB_DATABASE=todolist_test
CACHE_STORE=array
SESSION_DRIVER=array
```

### RefreshDatabase

Todos os testes Feature usam o trait `RefreshDatabase`:

```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
```

Isso garante que o banco seja limpo entre cada teste.

## � Sistema de Autenticação nos Testes

### Simulação de Sessões

O projeto implementa um sistema dual de autenticação (usuários + sessões). Para os testes, foi criado um sistema de simulação de sessões na classe `TestCase`:

```php
protected function createTarefaForSession(array $attributes = []): Tarefa
{
    return Tarefa::create(array_merge([
        'titulo' => 'Tarefa de Teste',
        'session_id' => session()->getId(),
        'user_id' => null
    ], $attributes));
}

protected function createTarefaForUser(User $user, array $attributes = []): Tarefa
{
    return Tarefa::create(array_merge([
        'titulo' => 'Tarefa de Teste',
        'user_id' => $user->id,
        'session_id' => null
    ], $attributes));
}
```

### Isolamento de Dados

- **Cada teste** executa em uma sessão isolada
- **Tarefas criadas** são automaticamente associadas à sessão do teste
- **Isolamento garantido** entre diferentes execuções de teste
- **Limpeza automática** do banco entre testes via `RefreshDatabase`

### Cobertura de Autenticação

- ✅ Isolamento por sessão (50 testes)
- ✅ Criação com associação automática à sessão
- ✅ Filtros respeitam isolamento de sessão
- ✅ Operações CRUD isoladas por sessão
- ⚠️ Testes de autenticação de usuários (em desenvolvimento)

## �📝 Detalhamento dos Testes por Categoria

### 1. Testes de Criação (CriarTest.php)

**Objetivo**: Verificar a criação de novas tarefas via POST `/api/tarefas`

```php
test('usuário pode adicionar nova tarefa com título e descrição', function () {
    $response = $this->postJson('/api/tarefas', [
        'titulo' => 'Nova Tarefa',
        'descricao' => 'Descrição da tarefa'
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'titulo', 'descricao', 'concluida', 
            'prioridade', 'data_vencimento', 'created_at', 'updated_at'
        ]);
});
```

**Cobertura**:
- ✅ Criação com título e descrição
- ✅ Validação de campos obrigatórios
- ✅ Criação apenas com título
- ✅ Criação com data de vencimento
- ✅ Definição de prioridades (alta, média, baixa)
- ✅ Valores padrão
- ✅ Validação de prioridades inválidas
- ✅ Criação completa com todos os campos

### 2. Testes de Listagem (ListagemTest.php)

**Objetivo**: Verificar listagem e filtros via GET `/api/tarefas`

```php
test('pode filtrar tarefas pendentes', function () {
    // Criar tarefas de teste
    Tarefa::create(['titulo' => 'Pendente 1', 'concluida' => false]);
    Tarefa::create(['titulo' => 'Concluída 1', 'concluida' => true]);

    $response = $this->getJson('/api/tarefas?estado=pendente');

    $response->assertStatus(200)
        ->assertJsonCount(1);
});
```

**Cobertura**:
- ✅ Listagem básica
- ✅ Filtros por estado (pendente/concluída)
- ✅ Filtros por prioridade
- ✅ Filtros por data de vencimento
- ✅ Filtros por tarefas vencidas
- ✅ Combinação de filtros
- ✅ Visualização de detalhes
- ✅ Tratamento de erros 404

### 3. Testes de Edição (EdicaoTest.php)

**Objetivo**: Verificar edição de tarefas via PUT `/api/tarefas/{id}`

```php
test('usuário pode editar título de uma tarefa existente', function () {
    $tarefa = Tarefa::create(['titulo' => 'Título Original']);

    $response = $this->putJson("/api/tarefas/{$tarefa->id}", [
        'titulo' => 'Título Editado'
    ]);

    $response->assertStatus(200)
        ->assertJson(['titulo' => 'Título Editado']);
});
```

**Cobertura**:
- ✅ Edição individual de campos
- ✅ Edição múltipla
- ✅ Validações
- ✅ Remoção de campos opcionais
- ✅ Preservação de campos não enviados
- ✅ Tratamento de erros

### 4. Testes de Conclusão (ConcluirTest.php)

**Objetivo**: Verificar marcação de tarefas como concluídas

```php
test('toggle alterna estado de pendente para concluída', function () {
    $tarefa = Tarefa::create(['titulo' => 'Tarefa', 'concluida' => false]);

    $response = $this->patchJson("/api/tarefas/{$tarefa->id}/toggle");

    $response->assertStatus(200)
        ->assertJson(['concluida' => true]);
});
```

**Cobertura**:
- ✅ Marcar como concluída via PUT
- ✅ Toggle via PATCH
- ✅ Alternância bidirecional
- ✅ Múltiplos toggles
- ✅ Preservação de outros campos
- ✅ Casos especiais (sem descrição, sem data, vencida)

### 5. Testes de Exclusão (ExcluirTest.php)

**Objetivo**: Verificar exclusão de tarefas via DELETE

```php
test('usuário pode excluir uma tarefa existente', function () {
    $tarefa = Tarefa::create(['titulo' => 'Tarefa para Excluir']);

    $response = $this->deleteJson("/api/tarefas/{$tarefa->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Tarefa excluída com sucesso']);

    $this->assertDatabaseMissing('tarefas', ['id' => $tarefa->id]);
});
```

## 🆕 Como Criar Novos Testes

### 1. Testes Feature (Recomendado)

Para testar funcionalidades completas da API:

```php
<?php

use App\Models\Tarefa;

test('nova funcionalidade específica', function () {
    // Arrange - Preparar dados
    $tarefa = Tarefa::create([
        'titulo' => 'Tarefa de Teste',
        'concluida' => false
    ]);

    // Act - Executar ação
    $response = $this->postJson('/api/tarefas/nova-rota', [
        'dados' => 'valor'
    ]);

    // Assert - Verificar resultado
    $response->assertStatus(200)
        ->assertJson(['esperado' => 'valor']);

    // Verificar banco de dados
    $this->assertDatabaseHas('tarefas', [
        'titulo' => 'Título Esperado'
    ]);
});
```

### 2. Testes Unit

Para testar métodos específicos de classes:

```php
<?php

use App\Models\Tarefa;

test('método específico do model funciona corretamente', function () {
    $tarefa = new Tarefa([
        'titulo' => 'Teste',
        'data_vencimento' => '2025-10-20'
    ]);

    expect($tarefa->estaVencida())->toBeTrue();
});
```

### 3. Convenções de Nomenclatura

```php
// ✅ Bom - Descreve comportamento esperado
test('usuário pode criar tarefa com data de vencimento futura', function () {});

// ❌ Ruim - Muito técnico
test('POST /api/tarefas com dataVencimento retorna 201', function () {});

// ✅ Bom - Contexto claro
test('retorna erro 422 quando título está vazio', function () {});

// ✅ Bom - Cenário específico
test('filtro por prioridade alta retorna apenas tarefas de alta prioridade', function () {});
```

## 🔍 Boas Práticas para Testes

### 1. Estrutura AAA (Arrange, Act, Assert)

```php
test('exemplo com estrutura AAA', function () {
    // Arrange - Preparar o cenário
    $tarefa = Tarefa::create(['titulo' => 'Teste']);
    
    // Act - Executar a ação
    $response = $this->getJson("/api/tarefas/{$tarefa->id}");
    
    // Assert - Verificar o resultado
    $response->assertStatus(200);
});
```

### 2. Dados de Teste Limpos

```php
// ✅ Bom - Dados específicos para o teste
Tarefa::create([
    'titulo' => 'Tarefa para Teste de Filtro',
    'prioridade' => 'alta',
    'concluida' => false
]);

// ❌ Evitar - Dados genéricos demais
Tarefa::create(['titulo' => 'Test']);
```

### 3. Asserções Específicas

```php
// ✅ Bom - Verificações específicas
$response->assertStatus(201)
    ->assertJsonStructure(['id', 'titulo', 'created_at'])
    ->assertJson(['titulo' => 'Título Esperado']);

// ✅ Bom - Verificar banco de dados
$this->assertDatabaseHas('tarefas', ['titulo' => 'Nova Tarefa']);
```

### 4. Testes Isolados

```php
// ✅ Cada teste deve ser independente
test('teste 1', function () {
    $tarefa = Tarefa::create(['titulo' => 'Teste 1']);
    // teste específico
});

test('teste 2', function () {
    $tarefa = Tarefa::create(['titulo' => 'Teste 2']);
    // outro teste independente
});
```

## 🐛 Debug e Troubleshooting

### Ver Resposta Completa

```php
test('debug de resposta', function () {
    $response = $this->getJson('/api/tarefas');
    
    // Ver conteúdo da resposta
    dump($response->json());
    
    // Ver status HTTP
    dump($response->status());
    
    $response->assertStatus(200);
});
```

### Verificar Estado do Banco

```php
test('debug do banco', function () {
    Tarefa::create(['titulo' => 'Teste']);
    
    // Ver quantos registros existem
    dump(Tarefa::count()); // Should be 1
    
    // Ver todos os registros
    dump(Tarefa::all()->toArray());
});
```

### Executar Teste Específico com Debug

```bash
# Executar apenas um teste para debug
./vendor/bin/pest --filter="nome do teste específico" -v
```

## 📊 Relatórios e Métricas

### Cobertura de Código

```bash
# Gerar relatório de cobertura
./vendor/bin/pest --coverage

# Cobertura em HTML (se configurado)
./vendor/bin/pest --coverage-html=coverage-report
```

### Relatório de Performance

```bash
# Ver tempo de execução dos testes
./vendor/bin/pest --profile
```

## 🔄 Integração Contínua

### Exemplo para GitHub Actions

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        
    - name: Install dependencies
      run: composer install
      
    - name: Setup test database
      run: |
        mysql -e 'CREATE DATABASE todolist_test;'
        php artisan migrate --env=testing
        
    - name: Run tests
      run: ./vendor/bin/pest
```

---

## 📚 Recursos Adicionais

- [Documentação do PestPHP](https://pestphp.com/docs)
- [Laravel Testing](https://laravel.com/docs/testing)
- [HTTP Tests no Laravel](https://laravel.com/docs/http-tests)
- [Database Testing](https://laravel.com/docs/database-testing)

**Pronto!** Agora você sabe tudo sobre os testes do projeto TodoList! 🧪✨