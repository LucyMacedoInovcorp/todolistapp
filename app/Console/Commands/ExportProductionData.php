<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Storage;

class ExportProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:export {--format=json : Format to export (json|sql)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exportar dados de usuários e tarefas para arquivo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->option('format');
        
        $this->info('Iniciando exportação de dados...');
        
        // Exportar usuários
        $users = User::all();
        $tarefas = Tarefa::with('user')->get();
        
        $this->info("Encontrados {$users->count()} usuários e {$tarefas->count()} tarefas");
        
        if ($format === 'sql') {
            $this->exportToSQL($users, $tarefas);
        } else {
            $this->exportToJSON($users, $tarefas);
        }
        
        $this->info('Exportação concluída!');
    }
    
    private function exportToJSON($users, $tarefas)
    {
        $data = [
            'export_date' => now()->toISOString(),
            'environment' => app()->environment(),
            'users' => $users->toArray(),
            'tarefas' => $tarefas->toArray(),
        ];
        
        $filename = 'backup_' . date('Y_m_d_H_i_s') . '.json';
        $path = storage_path('app/backups/' . $filename);
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->info("Dados exportados para: {$path}");
    }
    
    private function exportToSQL($users, $tarefas)
    {
        $sql = "-- Backup de dados - " . now()->toISOString() . "\n\n";
        
        // Exportar usuários
        $sql .= "-- Usuários\n";
        foreach ($users as $user) {
            $sql .= sprintf(
                "INSERT INTO users (id, name, email, email_verified_at, password, created_at, updated_at) VALUES (%d, %s, %s, %s, %s, %s, %s);\n",
                $user->id,
                $this->escapeSqlString($user->name),
                $this->escapeSqlString($user->email),
                $user->email_verified_at ? "'{$user->email_verified_at}'" : 'NULL',
                $this->escapeSqlString($user->password),
                "'{$user->created_at}'",
                "'{$user->updated_at}'"
            );
        }
        
        $sql .= "\n-- Tarefas\n";
        foreach ($tarefas as $tarefa) {
            $sql .= sprintf(
                "INSERT INTO tarefas (id, titulo, descricao, concluida, user_id, session_id, data_vencimento, prioridade, created_at, updated_at) VALUES (%d, %s, %s, %d, %s, %s, %s, %s, %s, %s);\n",
                $tarefa->id,
                $this->escapeSqlString($tarefa->titulo),
                $tarefa->descricao ? $this->escapeSqlString($tarefa->descricao) : 'NULL',
                $tarefa->concluida ? 1 : 0,
                $tarefa->user_id ? $tarefa->user_id : 'NULL',
                $tarefa->session_id ? $this->escapeSqlString($tarefa->session_id) : 'NULL',
                $tarefa->data_vencimento ? "'{$tarefa->data_vencimento}'" : 'NULL',
                $this->escapeSqlString($tarefa->prioridade),
                "'{$tarefa->created_at}'",
                "'{$tarefa->updated_at}'"
            );
        }
        
        $filename = 'backup_' . date('Y_m_d_H_i_s') . '.sql';
        $path = storage_path('app/backups/' . $filename);
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        file_put_contents($path, $sql);
        
        $this->info("SQL exportado para: {$path}");
    }
    
    private function escapeSqlString($string)
    {
        return "'" . str_replace("'", "''", $string) . "'";
    }
}
