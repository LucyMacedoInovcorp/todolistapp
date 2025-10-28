<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Dados - TodoList</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .actions {
            margin-bottom: 20px;
        }
        .priority-alta {
            color: #dc3545;
            font-weight: bold;
        }
        .priority-media {
            color: #ffc107;
            font-weight: bold;
        }
        .priority-baixa {
            color: #28a745;
            font-weight: bold;
        }
        .concluida {
            color: #28a745;
        }
        .pendente {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Administração de Dados - TodoList</h1>
        <p>Visualização e gerenciamento dos dados da aplicação</p>
        
        <div class="actions">
            <a href="{{ route('home') }}" class="btn">← Voltar ao TodoList</a>
            <button onclick="exportData()" class="btn btn-success">📥 Exportar Dados</button>
        </div>
    </div>

    <div class="container">
        <h2>Estatísticas</h2>
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_users'] }}</div>
                <div class="stat-label">Usuários Registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_tarefas'] }}</div>
                <div class="stat-label">Total de Tarefas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['tarefas_concluidas'] }}</div>
                <div class="stat-label">Tarefas Concluídas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['tarefas_sessao'] }}</div>
                <div class="stat-label">Tarefas por Sessão</div>
            </div>
        </div>
    </div>

    <div class="container">
        <h2>Usuários Registrados</h2>
        @if($users->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tarefas</th>
                        <th>Registrado em</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->tarefas_count }}</td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum usuário registrado.</p>
        @endif
    </div>

    <div class="container">
        <h2>Tarefas Recentes</h2>
        @if($tarefas->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Usuário</th>
                        <th>Prioridade</th>
                        <th>Status</th>
                        <th>Criada em</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tarefas as $tarefa)
                    <tr>
                        <td>{{ $tarefa->id }}</td>
                        <td>{{ Str::limit($tarefa->titulo, 50) }}</td>
                        <td>{{ $tarefa->user ? $tarefa->user->name : 'Sessão: ' . Str::limit($tarefa->session_id, 10) }}</td>
                        <td>
                            <span class="priority-{{ $tarefa->prioridade }}">
                                {{ ucfirst($tarefa->prioridade) }}
                            </span>
                        </td>
                        <td>
                            <span class="{{ $tarefa->concluida ? 'concluida' : 'pendente' }}">
                                {{ $tarefa->concluida ? 'Concluída' : 'Pendente' }}
                            </span>
                        </td>
                        <td>{{ $tarefa->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhuma tarefa encontrada.</p>
        @endif
    </div>

    <script>
        async function exportData() {
            try {
                // Pegar a chave da URL atual
                const urlParams = new URLSearchParams(window.location.search);
                const key = urlParams.get('key') || 'todolist2025';
                
                const response = await fetch('/admin/export?key=' + encodeURIComponent(key), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'todolist_backup_' + new Date().toISOString().slice(0,10) + '.json';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    alert('Dados exportados com sucesso!');
                } else {
                    alert('Erro ao exportar dados: ' + response.statusText);
                }
            } catch (error) {
                alert('Erro ao exportar dados: ' + error.message);
            }
        }
    </script>
</body>
</html>