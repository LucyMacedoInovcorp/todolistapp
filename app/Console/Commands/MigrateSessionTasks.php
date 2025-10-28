<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tarefa;

class MigrateSessionTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:migrate-session {session_id} {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra tarefas de uma sessão para um usuário logado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sessionId = $this->argument('session_id');
        $userId = $this->argument('user_id');
        
        $this->info("🔄 Migrando tarefas da sessão {$sessionId} para usuário {$userId}...");
        
        $sessionTasks = Tarefa::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();
        
        if ($sessionTasks->isEmpty()) {
            $this->info('✅ Nenhuma tarefa de sessão encontrada para migrar.');
            return 0;
        }
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($sessionTasks as $sessionTask) {
            // Verificar se o usuário já tem uma tarefa similar
            $existingTask = Tarefa::where('user_id', $userId)
                ->where('titulo', $sessionTask->titulo)
                ->where('descricao', $sessionTask->descricao)
                ->first();
            
            if ($existingTask) {
                // Usuário já tem tarefa similar, remover a da sessão
                $this->info("- Tarefa já existe para usuário: {$sessionTask->titulo}");
                $sessionTask->delete();
                $skipped++;
            } else {
                // Migrar tarefa para o usuário
                $sessionTask->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
                $this->info("✓ Migrada: {$sessionTask->titulo}");
                $migrated++;
            }
        }
        
        $this->info("\n📊 Resumo da migração:");
        $this->info("Tarefas migradas: {$migrated}");
        $this->info("Tarefas removidas (duplicadas): {$skipped}");
        
        return 0;
    }
}
