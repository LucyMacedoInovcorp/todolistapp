<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tarefa;

class SimpleImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:simple {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importação simples de dados de produção';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        
        // Procurar arquivo
        $paths = [
            $filename,
            storage_path('app/backups/' . $filename),
            storage_path('app/' . $filename),
        ];
        
        $filePath = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }
        
        if (!$filePath) {
            $this->error("Arquivo não encontrado: {$filename}");
            return 1;
        }
        
        $data = json_decode(file_get_contents($filePath), true);
        
        if (!$data) {
            $this->error("Erro ao ler arquivo JSON");
            return 1;
        }
        
        $this->info("Importando dados de produção...");
        $this->info("Data do backup: " . ($data['export_date'] ?? 'N/A'));
        
        // Importar apenas dados novos (merge)
        if (isset($data['users'])) {
            $this->info("Importando usuários...");
            foreach ($data['users'] as $userData) {
                $existing = User::where('email', $userData['email'])->first();
                
                if (!$existing) {
                    User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'email_verified_at' => $userData['email_verified_at'] ?? null,
                        'password' => $userData['password'] ?? bcrypt('password123'),
                        'created_at' => $userData['created_at'],
                        'updated_at' => $userData['updated_at'],
                    ]);
                    $this->info("✓ Usuário criado: {$userData['name']}");
                } else {
                    $this->info("- Usuário já existe: {$userData['name']}");
                }
            }
        }
        
        if (isset($data['tarefas'])) {
            $this->info("Importando tarefas...");
            foreach ($data['tarefas'] as $tarefaData) {
                // Verificação mais precisa para evitar duplicatas
                $query = Tarefa::where('titulo', $tarefaData['titulo'])
                    ->where('descricao', $tarefaData['descricao'] ?? '');
                
                // Verificar por user_id ou session_id
                if ($tarefaData['user_id']) {
                    $query->where('user_id', $tarefaData['user_id']);
                } else {
                    $query->where('session_id', $tarefaData['session_id']);
                }
                
                $existing = $query->first();
                
                if (!$existing) {
                    Tarefa::create([
                        'titulo' => $tarefaData['titulo'],
                        'descricao' => $tarefaData['descricao'],
                        'concluida' => $tarefaData['concluida'],
                        'user_id' => $tarefaData['user_id'],
                        'session_id' => $tarefaData['session_id'],
                        'data_vencimento' => $tarefaData['data_vencimento'],
                        'prioridade' => $tarefaData['prioridade'],
                        'created_at' => $tarefaData['created_at'],
                        'updated_at' => $tarefaData['updated_at'],
                    ]);
                    
                    $owner = $tarefaData['user_id'] ? "usuário {$tarefaData['user_id']}" : "sessão " . substr($tarefaData['session_id'], 0, 8);
                    $this->info("✓ Tarefa criada: {$tarefaData['titulo']} ({$owner})");
                } else {
                    $owner = $existing->user_id ? "usuário {$existing->user_id}" : "sessão " . substr($existing->session_id, 0, 8);
                    $this->info("- Tarefa já existe: {$tarefaData['titulo']} ({$owner})");
                }
            }
        }
        
        $this->info("\n📊 Totais atuais:");
        $this->info("Usuários: " . User::count());
        $this->info("Tarefas: " . Tarefa::count());
        
        $this->info("\n✅ Importação concluída!");
        
        return 0;
    }
}
