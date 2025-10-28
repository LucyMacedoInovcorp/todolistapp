<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tarefa;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    /**
     * Mostrar painel de administração de dados
     */
    public function showData(Request $request)
    {
        // Verificação simples de acesso (opcional)
        $adminKey = $request->get('key');
        $expectedKey = env('ADMIN_KEY', 'todolist2025');
        
        if ($adminKey !== $expectedKey) {
            return response()->view('admin.login', ['current_url' => $request->fullUrl()]);
        }
        
        $stats = [
            'total_users' => User::count(),
            'total_tarefas' => Tarefa::count(),
            'tarefas_concluidas' => Tarefa::where('concluida', true)->count(),
            'tarefas_sessao' => Tarefa::whereNull('user_id')->count(),
        ];

        $users = User::withCount('tarefas')->orderBy('created_at', 'desc')->take(50)->get();
        $tarefas = Tarefa::with('user')->orderBy('created_at', 'desc')->take(100)->get();

        return view('admin.data', compact('stats', 'users', 'tarefas'));
    }

    /**
     * Exportar dados para download
     */
    public function exportData(Request $request)
    {
        // Verificação simples de acesso
        $adminKey = $request->get('key');
        $expectedKey = env('ADMIN_KEY', 'todolist2025');
        
        if ($adminKey !== $expectedKey) {
            abort(403, 'Acesso negado');
        }
        
        $users = User::all();
        $tarefas = Tarefa::with('user')->get();
        
        $data = [
            'export_date' => now()->toISOString(),
            'environment' => app()->environment(),
            'host' => request()->getHost(),
            'stats' => [
                'total_users' => $users->count(),
                'total_tarefas' => $tarefas->count(),
                'tarefas_concluidas' => $tarefas->where('concluida', true)->count(),
                'tarefas_sessao' => $tarefas->whereNull('user_id')->count(),
            ],
            'users' => $users->toArray(),
            'tarefas' => $tarefas->toArray(),
        ];

        $filename = 'todolist_backup_' . date('Y_m_d_H_i_s') . '.json';
        
        return Response::json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Mostrar estatísticas em JSON
     */
    public function getStats()
    {
        $stats = [
            'total_users' => User::count(),
            'total_tarefas' => Tarefa::count(),
            'tarefas_concluidas' => Tarefa::where('concluida', true)->count(),
            'tarefas_pendentes' => Tarefa::where('concluida', false)->count(),
            'tarefas_sessao' => Tarefa::whereNull('user_id')->count(),
            'tarefas_usuario' => Tarefa::whereNotNull('user_id')->count(),
            'users_with_tasks' => User::has('tarefas')->count(),
            'latest_user' => User::latest()->first()?->created_at,
            'latest_tarefa' => Tarefa::latest()->first()?->created_at,
        ];

        return response()->json($stats);
    }
}
