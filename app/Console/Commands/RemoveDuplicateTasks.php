<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tarefa;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:clean-duplicates {--dry-run : Mostrar duplicatas sem remover}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove tarefas duplicadas mantendo apenas a mais antiga';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Procurando por tarefas duplicadas...');
        
        // Buscar duplicatas por título, descrição, user_id e session_id
        $duplicateGroups = DB::table('tarefas')
            ->select('titulo', 'descricao', 'user_id', 'session_id', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('titulo', 'descricao', 'user_id', 'session_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        
        if ($duplicateGroups->isEmpty()) {
            $this->info('✅ Nenhuma duplicata encontrada!');
            return 0;
        }
        
        $this->info("🔍 Encontrados {$duplicateGroups->count()} grupos de duplicatas:");
        
        $totalToRemove = 0;
        
        foreach ($duplicateGroups as $group) {
            // Buscar todas as tarefas deste grupo
            $query = Tarefa::where('titulo', $group->titulo)
                ->where('descricao', $group->descricao ?? '');
                
            if ($group->user_id) {
                $query->where('user_id', $group->user_id);
            } else {
                $query->where('session_id', $group->session_id);
            }
            
            $tasks = $query->orderBy('id')->get();
            
            $owner = $group->user_id ? "Usuário ID: {$group->user_id}" : "Sessão: " . substr($group->session_id, 0, 10);
            
            $this->info("\n📝 Tarefa: \"{$group->titulo}\"");
            $this->info("   Proprietário: {$owner}");
            $this->info("   Duplicatas: {$group->count}");
            
            // Mostrar detalhes de cada duplicata
            foreach ($tasks as $index => $task) {
                $status = $index === 0 ? '✅ MANTER' : '❌ REMOVER';
                $this->info("   ID {$task->id}: {$task->created_at} - {$status}");
                
                if ($index > 0) {
                    $totalToRemove++;
                }
            }
            
            // Remover duplicatas (manter apenas a primeira/mais antiga)
            if (!$dryRun && $tasks->count() > 1) {
                $toDelete = $tasks->skip(1); // Pular a primeira (mais antiga)
                foreach ($toDelete as $task) {
                    $task->delete();
                    $this->info("   🗑️ Removida tarefa ID: {$task->id}");
                }
            }
        }
        
        $this->info("\n📊 Resumo:");
        $this->info("Grupos de duplicatas: {$duplicateGroups->count()}");
        $this->info("Tarefas a remover: {$totalToRemove}");
        
        if ($dryRun) {
            $this->info("\n⚠️ Modo de visualização ativo. Para remover as duplicatas, execute:");
            $this->info("php artisan tasks:clean-duplicates");
        } else {
            $this->info("\n✅ Duplicatas removidas com sucesso!");
            
            // Mostrar estatísticas finais
            $this->info("\n📈 Estatísticas atuais:");
            $this->info("Total de tarefas: " . Tarefa::count());
            $this->info("Tarefas de usuários: " . Tarefa::whereNotNull('user_id')->count());
            $this->info("Tarefas de sessão: " . Tarefa::whereNull('user_id')->count());
        }
        
        return 0;
    }
}
