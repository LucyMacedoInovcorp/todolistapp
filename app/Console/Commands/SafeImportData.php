<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tarefa;
use Illuminate\Support\Facades\DB;

class SafeImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:safe-import {file : Arquivo JSON para importar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importação segura de dados com opções flexíveis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        
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
            return 1;
        }
        
        $this->info("Lendo arquivo: {$filePath}");
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (!$data) {
            $this->error("Erro ao decodificar JSON");
            return 1;
        }
        
        $this->info("📊 Informações do Backup:");
        $this->info("Data: " . ($data['export_date'] ?? 'desconhecida'));
        $this->info("Ambiente: " . ($data['environment'] ?? 'desconhecido'));
        
        if (isset($data['stats'])) {
            $this->info("Usuários: " . ($data['stats']['total_users'] ?? 0));
            $this->info("Tarefas: " . ($data['stats']['total_tarefas'] ?? 0));
        }
        
        $this->info("\n📈 Dados Atuais:");
        $this->info("Usuários: " . User::count());
        $this->info("Tarefas: " . Tarefa::count());
        
        // Opções de importação
        $option = $this->choice(
            'Como deseja importar os dados?',
            [
                'merge' => 'Mesclar (adicionar novos, manter existentes)',
                'replace_all' => 'Substituir todos os dados',
                'users_only' => 'Importar apenas usuários',
                'tarefas_only' => 'Importar apenas tarefas',
                'cancel' => 'Cancelar'
            ],
            'merge'
        );
        
        if ($option === 'cancel') {
            $this->info('Operação cancelada.');
            return 0;
        }
        
        try {
            DB::beginTransaction();
            
            switch ($option) {
                case 'replace_all':
                    $this->clearAllData();
                    $this->importUsers($data);
                    $this->importTarefas($data);
                    break;
                    
                case 'users_only':
                    if ($this->confirm('Limpar usuários existentes antes de importar?')) {
                        $this->clearUsers();
                    }
                    $this->importUsers($data, true);
                    break;
                    
                case 'tarefas_only':
                    if ($this->confirm('Limpar tarefas existentes antes de importar?')) {
                        $this->clearTarefas();
                    }
                    $this->importTarefas($data, true);
                    break;
                    
                case 'merge':
                default:
                    $this->importUsers($data, true);
                    $this->importTarefas($data, true);
                    break;
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Erro durante a importação: ' . $e->getMessage());
            return 1;
        }
        
        $this->info('✅ Importação concluída com sucesso!');
        
        // Mostrar estatísticas finais
        $this->info("\n📊 Estatísticas Finais:");
        $this->info("Usuários: " . User::count());
        $this->info("Tarefas: " . Tarefa::count());
    }
    
    private function clearAllData()
    {
        $this->info('🗑️ Limpando todos os dados...');
        $this->clearTarefas();
        $this->clearUsers();
    }
    
    private function clearUsers()
    {
        $this->info('Removendo usuários...');
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('users')->delete();
            DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $e) {
            User::query()->delete();
        }
    }
    
    private function clearTarefas()
    {
        $this->info('Removendo tarefas...');
        try {
            DB::table('tarefas')->delete();
            DB::statement('ALTER TABLE tarefas AUTO_INCREMENT = 1');
        } catch (\Exception $e) {
            Tarefa::query()->delete();
        }
    }
    
    private function importUsers($data, $skipExisting = false)
    {
        if (!isset($data['users']) || empty($data['users'])) {
            $this->info('Nenhum usuário para importar.');
            return;
        }
        
        $imported = 0;
        $skipped = 0;
        
        $this->info("Importando usuários...");
        
        foreach ($data['users'] as $userData) {
            if ($skipExisting) {
                $existing = User::where('email', $userData['email'])->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }
            }
            
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => $userData['email_verified_at'] ?? null,
                'password' => $userData['password'] ?? bcrypt('password123'), // senha padrão se não houver
                'created_at' => $userData['created_at'],
                'updated_at' => $userData['updated_at'],
            ]);
            
            $imported++;
        }
        
        $this->info("Usuários importados: {$imported}, ignorados: {$skipped}");
    }
    
    private function importTarefas($data, $skipExisting = false)
    {
        if (!isset($data['tarefas']) || empty($data['tarefas'])) {
            $this->info('Nenhuma tarefa para importar.');
            return;
        }
        
        $imported = 0;
        $skipped = 0;
        
        $this->info("Importando tarefas...");
        
        foreach ($data['tarefas'] as $tarefaData) {
            if ($skipExisting) {
                // Verificar duplicatas por título e data de criação
                $existing = Tarefa::where('titulo', $tarefaData['titulo'])
                    ->where('created_at', $tarefaData['created_at'])
                    ->first();
                if ($existing) {
                    $skipped++;
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
            
            $imported++;
        }
        
        $this->info("Tarefas importadas: {$imported}, ignoradas: {$skipped}");
    }
}
