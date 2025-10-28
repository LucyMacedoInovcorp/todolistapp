# 📚 Documentação da API - TodoList

## Informações Gerais

- **Base URL**: `https://todolistapp-main-sawgdc.laravel.cloud/api`
- **Formato**: JSON
- **Autenticação**: Opcional - funciona com e sem login
- **Content-Type**: `application/json`

## 🔐 Sistema de Autenticação

A API funciona de **duas maneiras**:

### 📱 **Usuários Não Autenticados (Sessão)**
- Tarefas isoladas por sessão do navegador
- Dados temporários (perdidos ao limpar navegador)
- Não requer cadastro ou login

### 👤 **Usuários Autenticados (Conta)**
- Tarefas salvas permanentemente no banco
- Acesso de qualquer dispositivo
- Isolamento total por usuário

### 🔑 **Autenticação por Token**
Para usar com autenticação, inclua o header:
```http
Authorization: Bearer {seu-token-aqui}
```

## � Endpoints de Autenticação

### 1. Registrar Usuário

```http
POST /api/register
```

Cria uma nova conta de usuário.

**Body (JSON):**
```json
{
  "name": "João Silva",
  "email": "joao@email.com", 
  "password": "minimo8chars",
  "password_confirmation": "minimo8chars"
}
```

**Resposta de Sucesso (201):**
```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@email.com"
  },
  "token": "1|abc123...",
  "message": "Usuário registrado com sucesso!"
}
```

### 2. Fazer Login

```http
POST /api/login
```

Autentica um usuário existente.

**Body (JSON):**
```json
{
  "email": "joao@email.com",
  "password": "minimo8chars"
}
```

**Resposta de Sucesso (200):**
```json
{
  "user": {
    "id": 1,
    "name": "João Silva", 
    "email": "joao@email.com"
  },
  "token": "1|xyz789...",
  "message": "Login realizado com sucesso!"
}
```

### 3. Logout

```http
POST /api/logout
```

**Headers:**
```http
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "message": "Logout realizado com sucesso!"
}
```

### 4. Dados do Usuário

```http
GET /api/user
```

**Headers:**
```http
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@email.com"
  }
}
```

##  Endpoints das Tarefas

> **💡 Importante:** Todos os endpoints de tarefas funcionam automaticamente com isolamento:
> - **Com token:** Mostra apenas tarefas do usuário autenticado
> - **Sem token:** Mostra apenas tarefas da sessão atual

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
# Listar tarefas da sessão (sem token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas" \
  -H "Accept: application/json"

# Listar tarefas do usuário autenticado (com token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."

# Filtrar tarefas pendentes de alta prioridade (com token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?estado=pendente&prioridade=alta" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."

# Filtrar tarefas por data de vencimento (sem token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?data_vencimento=2025-10-25" \
  -H "Accept: application/json"

# Listar tarefas vencidas (com token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?vencidas=true" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."
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
# Criar tarefa da sessão (sem token)
curl -X POST "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "titulo": "Nova tarefa",
    "descricao": "Descrição da nova tarefa",
    "dataVencimento": "2025-10-30",
    "prioridade": "alta"
  }'

# Criar tarefa do usuário (com token)
curl -X POST "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..." \
  -d '{
    "titulo": "Tarefa do usuário",
    "descricao": "Tarefa vinculada ao usuário autenticado",
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
# Visualizar tarefa da sessão (sem token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Accept: application/json"

# Visualizar tarefa do usuário (com token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."
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
# Editar tarefa da sessão (sem token)
curl -X PUT "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "titulo": "Estudar Laravel Avançado",
    "descricao": "Focar em relacionamentos e migrações",
    "prioridade": "alta"
  }'

# Editar tarefa do usuário (com token)
curl -X PUT "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..." \
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
# Alternar status da tarefa da sessão (sem token)
curl -X PATCH "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1/toggle" \
  -H "Accept: application/json"

# Alternar status da tarefa do usuário (com token)
curl -X PATCH "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1/toggle" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."
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
# Excluir tarefa da sessão (sem token)
curl -X DELETE "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Accept: application/json"

# Excluir tarefa do usuário (com token)
curl -X DELETE "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."
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
| `201` | Created | Tarefa ou usuário criado com sucesso |
| `401` | Unauthorized | Token inválido ou expirado |
| `404` | Not Found | Tarefa não encontrada ou credenciais inválidas (login) |
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
# Listar tarefas vencidas não concluídas (com token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?vencidas=true" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."

# Tarefas de hoje (sem token - da sessão)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?data_vencimento=2025-10-21" \
  -H "Accept: application/json"

# Tarefas concluídas de prioridade baixa (com token)
curl -X GET "https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas?estado=concluida&prioridade=baixa" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|xyz789..."
```

---

## 🔐 Considerações Importantes sobre Autenticação

### Isolamento Automático de Dados

A API implementa **isolamento automático** baseado no contexto da requisição:

- **🔑 Com Token:** Todas as operações são filtradas pelo `user_id` do usuário autenticado
- **🍪 Sem Token:** Todas as operações são filtradas pela `session_id` atual

### Migração entre Modos

- **Sessão → Usuário:** Ao fazer login, as tarefas da sessão **NÃO** são transferidas automaticamente
- **Usuário → Sessão:** Ao fazer logout, volta a ver apenas tarefas da sessão

### Tokens de Autenticação

- **Formato:** `Bearer {token}` no header `Authorization`
- **Validade:** Tokens não expiram automaticamente (persistentes)  
- **Revogação:** Use o endpoint `/api/logout` para invalidar o token atual

### Comportamento de Erro 404

Quando uma tarefa não é encontrada, pode significar:
1. A tarefa não existe no banco de dados
2. A tarefa existe, mas pertence a outro usuário/sessão
3. Você não tem permissão para acessá-la

Por questões de segurança, a API retorna sempre `404` nesses casos.
