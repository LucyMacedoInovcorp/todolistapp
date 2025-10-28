# 📚 Documentação da API - TodoList

## Informações Gerais

- **Base URL**: `https://todolistapp-main-sawgdc.laravel.cloud/api`
- **Formato**: JSON
- **Autenticação**: Não requerida (para esta versão)
- **Content-Type**: `application/json`

## 📋 Endpoints das Tarefas

### 1. Listar Tarefas

```http
GET /api/tarefas
```

Lista todas as tarefas com opções de filtro.

#### Parâmetros de Query (Opcionais)

| Parâmetro | Tipo | Valores | Descrição |
|-----------|------|---------|-----------|
| `estado` | string | `pendente`, `concluida`, `todas` | Filtra por status da tarefa |
| `prioridade` | string | `alta`, `media`, `baixa` | Filtra por prioridade |
| `data_vencimento` | string | `YYYY-MM-DD` | Filtra por data específica |
| `vencidas` | string | `true`, `false` | Mostra apenas tarefas vencidas |

#### Exemplos de Requisição

```bash
# Listar todas as tarefas
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas"

# Filtrar tarefas pendentes de alta prioridade
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?estado=pendente&prioridade=alta"

# Filtrar tarefas por data de vencimento
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?data_vencimento=2025-10-25"

# Listar tarefas vencidas
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?vencidas=true"
```

#### Resposta de Sucesso (200)

```json
[
  {
    "id": 1,
    "titulo": "Estudar Laravel",
    "descricao": "Revisar conceitos de Eloquent ORM",
    "concluida": false,
    "prioridade": "alta",
    "data_vencimento": "2025-10-25T00:00:00.000000Z",
    "created_at": "2025-10-21T10:30:00.000000Z",
    "updated_at": "2025-10-21T10:30:00.000000Z"
  },
  {
    "id": 2,
    "titulo": "Fazer exercícios",
    "descricao": "Treino de 30 minutos",
    "concluida": true,
    "prioridade": "media",
    "data_vencimento": null,
    "created_at": "2025-10-20T08:00:00.000000Z",
    "updated_at": "2025-10-21T09:15:00.000000Z"
  }
]
```

---

### 2. Criar Tarefa

```http
POST /api/tarefas
```

Cria uma nova tarefa.

#### Body da Requisição

```json
{
  "titulo": "string (obrigatório, máx 255 chars)",
  "descricao": "string (opcional)",
  "dataVencimento": "string (opcional, formato: YYYY-MM-DD)",
  "prioridade": "string (opcional: alta|media|baixa, padrão: media)"
}
```

#### Exemplo de Requisição

```bash
curl -X POST "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Nova tarefa",
    "descricao": "Descrição da nova tarefa",
    "dataVencimento": "2025-10-30",
    "prioridade": "alta"
  }'
```

#### Resposta de Sucesso (201)

```json
{
  "id": 3,
  "titulo": "Nova tarefa",
  "descricao": "Descrição da nova tarefa",
  "concluida": false,
  "prioridade": "alta",
  "data_vencimento": "2025-10-30T00:00:00.000000Z",
  "created_at": "2025-10-21T14:30:00.000000Z",
  "updated_at": "2025-10-21T14:30:00.000000Z"
}
```

#### Resposta de Erro (422)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "titulo": [
      "The titulo field is required."
    ],
    "prioridade": [
      "The selected prioridade is invalid."
    ]
  }
}
```

---

### 3. Visualizar Tarefa

```http
GET /api/tarefas/{id}
```

Retorna os detalhes de uma tarefa específica.

#### Parâmetros de URL

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | ID da tarefa |

#### Exemplo de Requisição

```bash
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1"
```

#### Resposta de Sucesso (200)

```json
{
  "id": 1,
  "titulo": "Estudar Laravel",
  "descricao": "Revisar conceitos de Eloquent ORM",
  "concluida": false,
  "prioridade": "alta",
  "data_vencimento": "2025-10-25T00:00:00.000000Z",
  "created_at": "2025-10-21T10:30:00.000000Z",
  "updated_at": "2025-10-21T10:30:00.000000Z"
}
```

#### Resposta de Erro (404)

```json
{
  "message": "No query results for model [App\\Models\\Tarefa] 999"
}
```

---

### 4. Editar Tarefa

```http
PUT /api/tarefas/{id}
```

Atualiza uma tarefa existente.

#### Parâmetros de URL

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | ID da tarefa |

#### Body da Requisição

```json
{
  "titulo": "string (obrigatório se enviado, máx 255 chars)",
  "descricao": "string (opcional, null para remover)",
  "concluida": "boolean (opcional)",
  "dataVencimento": "string (opcional, formato: YYYY-MM-DD, null para remover)",
  "prioridade": "string (opcional: alta|media|baixa)"
}
```

#### Exemplo de Requisição

```bash
curl -X PUT "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Estudar Laravel Avançado",
    "descricao": "Focar em relacionamentos e migrações",
    "prioridade": "alta"
  }'
```

#### Resposta de Sucesso (200)

```json
{
  "id": 1,
  "titulo": "Estudar Laravel Avançado",
  "descricao": "Focar em relacionamentos e migrações",
  "concluida": false,
  "prioridade": "alta",
  "data_vencimento": "2025-10-25T00:00:00.000000Z",
  "created_at": "2025-10-21T10:30:00.000000Z",
  "updated_at": "2025-10-21T14:45:00.000000Z"
}
```

---

### 5. Alternar Status da Tarefa

```http
PATCH /api/tarefas/{id}/toggle
```

Alterna o status da tarefa entre concluída e pendente.

#### Parâmetros de URL

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | ID da tarefa |

#### Exemplo de Requisição

```bash
curl -X PATCH "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1/toggle"
```

#### Resposta de Sucesso (200)

```json
{
  "id": 1,
  "titulo": "Estudar Laravel Avançado",
  "descricao": "Focar em relacionamentos e migrações",
  "concluida": true,
  "prioridade": "alta",
  "data_vencimento": "2025-10-25T00:00:00.000000Z",
  "created_at": "2025-10-21T10:30:00.000000Z",
  "updated_at": "2025-10-21T15:00:00.000000Z"
}
```

---

### 6. Excluir Tarefa

```http
DELETE /api/tarefas/{id}
```

Remove uma tarefa permanentemente.

#### Parâmetros de URL

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `id` | integer | ID da tarefa |

#### Exemplo de Requisição

```bash
curl -X DELETE "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1"
```

#### Resposta de Sucesso (200)

```json
{
  "message": "Tarefa excluída com sucesso"
}
```

---

## 📊 Códigos de Status HTTP

| Código | Significado | Quando Ocorre |
|--------|-------------|---------------|
| `200` | OK | Operação realizada com sucesso |
| `201` | Created | Tarefa criada com sucesso |
| `404` | Not Found | Tarefa não encontrada |
| `422` | Unprocessable Entity | Dados de entrada inválidos |
| `405` | Method Not Allowed | Método HTTP não permitido para o endpoint |

## 🏗️ Estrutura dos Dados

### Objeto Tarefa

```json
{
  "id": "integer - ID único da tarefa",
  "titulo": "string - Título da tarefa (obrigatório, máx 255 chars)",
  "descricao": "string|null - Descrição detalhada (opcional)",
  "concluida": "boolean - Status de conclusão (padrão: false)",
  "prioridade": "string - Prioridade (alta|media|baixa, padrão: media)",
  "data_vencimento": "string|null - Data de vencimento ISO 8601 ou null",
  "created_at": "string - Data de criação ISO 8601",
  "updated_at": "string - Data da última atualização ISO 8601"
}
```

## 🔧 Exemplos de Uso Completos

### Fluxo Completo de uma Tarefa

```bash
# 1. Criar uma nova tarefa
curl -X POST "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Implementar API",
    "descricao": "Desenvolver endpoints RESTful",
    "dataVencimento": "2025-11-01",
    "prioridade": "alta"
  }'

# 2. Listar tarefas pendentes de alta prioridade
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?estado=pendente&prioridade=alta"

# 3. Marcar como concluída
curl -X PATCH "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1/toggle"

# 4. Editar descrição
curl -X PUT "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Content-Type: application/json" \
  -d '{
    "descricao": "API RESTful implementada com sucesso"
  }'

# 5. Visualizar detalhes
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1"
```

### Cenários Comuns de Filtros

```bash
# Listar tarefas vencidas não concluídas
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?vencidas=true"

# Tarefas de hoje
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?data_vencimento=2025-10-21"

# Tarefas concluídas de prioridade baixa
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?estado=concluida&prioridade=baixa"
```
