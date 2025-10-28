<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tarefa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import {file : Arquivo JSON para importar} {--merge : Mesclar com dados existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar dados de usuários e tarefas de arquivo JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $merge = $this->option('merge');
        
        // Procurar o arquivo
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
            $this->info("Procurado em:");
            foreach ($paths as $path) {
                $this->info("  - {$path}");
            }
            return 1;
        }
        
        $this->info("Lendo arquivo: {$filePath}");
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (!$data) {
            $this->error("Erro ao decodificar JSON");
            return 1;
        }
        
        $this->info("Backup de: " . ($data['export_date'] ?? 'data desconhecida'));
        $this->info("Ambiente: " . ($data['environment'] ?? 'desconhecido'));
        
        if (!$merge && !$this->confirm('Isso irá substituir todos os dados existentes. Continuar?')) {
            $this->info('Operação cancelada.');
            return 0;
        }
        
        DB::transaction(function () use ($data, $merge) {
            if (!$merge) {
                // Limpar dados existentes
                $this->info('Limpando dados existentes...');
                Tarefa::truncate();
                User::truncate();
                
                // Resetar auto increment
                DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
                DB::statement('ALTER TABLE tarefas AUTO_INCREMENT = 1');
            }
            
            // Importar usuários
            if (isset($data['users'])) {
                $userCount = count($data['users']);
                $this->info("Importando {$userCount} usuários...");
                foreach ($data['users'] as $userData) {
                    if ($merge) {
                        // Verificar se usuário já existe
                        $existingUser = User::where('email', $userData['email'])->first();
                        if ($existingUser) {
                            $this->info("Usuário já existe: {$userData['email']}");
                            continue;
                        }
                    }
                    
                    User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'email_verified_at' => $userData['email_verified_at'],
                        'password' => $userData['password'], // Já está hasheada
                        'created_at' => $userData['created_at'],
                        'updated_at' => $userData['updated_at'],
                    ]);
                }
            }
            
            // Importar tarefas
            if (isset($data['tarefas'])) {
                $tarefaCount = count($data['tarefas']);
                $this->info("Importando {$tarefaCount} tarefas...");
                foreach ($data['tarefas'] as $tarefaData) {
                    if ($merge) {
                        // Verificar se tarefa já existe
                        $existingTarefa = Tarefa::where('titulo', $tarefaData['titulo'])
                            ->where('created_at', $tarefaData['created_at'])
                            ->first();
                        if ($existingTarefa) {
                            continue;
                        }
                    }
                    
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
                }
            }
        });
        
        $this->info('Importação concluída com sucesso!');
        
        // Mostrar estatísticas
        $userCount = User::count();
        $tarefaCount = Tarefa::count();
        
        $this->info("Total atual: {$userCount} usuários, {$tarefaCount} tarefas");
    }
}
