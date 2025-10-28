<?php

namespace App\Http\Controllers;

use App\Models\Tarefa;
use Illuminate\Http\Request;

class TarefaController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarefa::query();

        // Isolamento por usuário ou sessão
        $this->applyUserOrSessionFilter($query, $request);

        // Filtro por estado (pendente/concluida)
        if ($request->has('estado')) {
            if ($request->estado === 'pendente') {
                $query->where('concluida', false);
            } elseif ($request->estado === 'concluida') {
                $query->where('concluida', true);
            }
        }

        // Filtro por prioridade
        if ($request->has('prioridade')) {
            $query->where('prioridade', $request->prioridade);
        }

        // Filtro por data de vencimento específica
        if ($request->has('data_vencimento')) {
            $query->whereDate('data_vencimento', $request->data_vencimento);
        }

        // Filtro por tarefas vencidas
        if ($request->has('vencidas') && $request->vencidas === 'true') {
            $query->where('data_vencimento', '<', now()->toDateString())
                  ->where('concluida', false);
        }

        $tarefas = $query->orderBy('created_at', 'desc')->get();
        return response()->json($tarefas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'dataVencimento' => 'nullable|date',
            'prioridade' => 'nullable|in:baixa,media,alta',
        ]);

        $data = [
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'data_vencimento' => $request->dataVencimento,
            'prioridade' => $request->prioridade ?? 'media',
            'concluida' => false,
        ];

        // Associar à sessão ou usuário
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        } else {
            $data['session_id'] = session('test_session_id', session()->getId());
        }

        $tarefa = Tarefa::create($data);

        return response()->json($tarefa, 201);
    }

    public function show(Request $request, Tarefa $tarefa)
    {
        // Verificar se a tarefa pertence ao usuário atual ou sessão
        if (!$this->userCanAccessTarefa($request, $tarefa)) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        return response()->json($tarefa);
    }

    public function update(Request $request, Tarefa $tarefa)
    {
        // Verificar se a tarefa pertence ao usuário atual ou sessão
        if (!$this->userCanAccessTarefa($request, $tarefa)) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'concluida' => 'sometimes|boolean',
            'dataVencimento' => 'nullable|date',
            'prioridade' => 'nullable|in:baixa,media,alta',
        ]);

        $updateData = $request->only(['titulo', 'descricao', 'concluida', 'prioridade']);
        if ($request->has('dataVencimento')) {
            $updateData['data_vencimento'] = $request->dataVencimento;
        }

        $tarefa->update($updateData);

        return response()->json($tarefa);
    }

    public function destroy(Request $request, Tarefa $tarefa)
    {
        // Verificar se a tarefa pertence ao usuário atual ou sessão
        if (!$this->userCanAccessTarefa($request, $tarefa)) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        $tarefa->delete();
        return response()->json(['message' => 'Tarefa excluída com sucesso']);
    }

    public function toggleComplete(Request $request, Tarefa $tarefa)
    {
        // Verificar se a tarefa pertence ao usuário atual ou sessão
        if (!$this->userCanAccessTarefa($request, $tarefa)) {
            return response()->json(['message' => 'Tarefa não encontrada'], 404);
        }

        $tarefa->update(['concluida' => !$tarefa->concluida]);
        return response()->json($tarefa);
    }

    /**
     * Aplicar filtro de usuário ou sessão nas consultas
     */
    private function applyUserOrSessionFilter($query, Request $request)
    {
        if ($request->user()) {
            // Usuário autenticado: mostrar apenas suas tarefas
            $query->where('user_id', $request->user()->id);
        } else {
            // Usuário não autenticado: usar sessão
            $sessionId = session('test_session_id', session()->getId());
            $query->where('session_id', $sessionId);
        }
    }

    /**
     * Verificar se o usuário pode acessar a tarefa
     */
    private function userCanAccessTarefa(Request $request, Tarefa $tarefa)
    {
        if ($request->user()) {
            // Usuário autenticado: verificar se é dono da tarefa
            return $tarefa->user_id == $request->user()->id;
        } else {
            // Usuário não autenticado: verificar sessão
            $sessionId = session('test_session_id', session()->getId());
            return $tarefa->session_id == $sessionId;
        }
    }
}
