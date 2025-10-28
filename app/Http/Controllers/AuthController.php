<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar novo usuário
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Se a requisição espera JSON, retorna JSON
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'message' => 'Usuário registrado com sucesso!'
                ], 201);
            }

            // Caso contrário, redireciona para home com o token na sessão
            session(['auth_token' => $token]);
            session(['user' => $user]);
            return redirect('/')->with('success', 'Conta criada com sucesso!');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                throw $e;
            }
            
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Fazer login do usuário
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciais inválidas.'],
                ]);
            }

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('auth_token')->plainTextToken;

            // Migrar tarefas de sessão para o usuário (se houver)
            $this->migrateSessionTasks($request, $user->id);

            // Se a requisição espera JSON, retorna JSON
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'user' => $user,
                    'token' => $token,
                    'message' => 'Login realizado com sucesso!'
                ]);
            }

            // Caso contrário, redireciona para home com o token na sessão
            session(['auth_token' => $token]);
            session(['user' => $user]);
            return redirect('/')->with('success', 'Login realizado com sucesso!');

        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                throw $e;
            }
            
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso!'
        ]);
    }

    /**
     * Obter dados do usuário logado
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    /**
     * Mostrar página de login
     */
    public function showLogin()
    {
        // Detectar se é produção ou se há problemas com JavaScript
        $userAgent = request()->header('User-Agent', '');
        $isOldBrowser = strpos($userAgent, 'MSIE') !== false || 
                       strpos($userAgent, 'Trident') !== false;
        
        if ($isOldBrowser || app()->environment('production')) {
            return view('auth.login_fallback');
        }
        
        return view('auth.login_standalone');
    }

    /**
     * Mostrar página de registro
     */
    public function showRegister()
    {
        // Detectar se é produção ou se há problemas com JavaScript
        $userAgent = request()->header('User-Agent', '');
        $isOldBrowser = strpos($userAgent, 'MSIE') !== false || 
                       strpos($userAgent, 'Trident') !== false;
        
        if ($isOldBrowser || app()->environment('production')) {
            return view('auth.register_fallback');
        }
        
        return view('auth.register_standalone');
    }

    /**
     * Migrar tarefas de sessão para usuário logado
     */
    private function migrateSessionTasks(Request $request, $userId)
    {
        // Tentar obter session ID de várias fontes
        $sessionId = $request->session()->getId() ?? 
                    $request->header('X-Session-ID') ?? 
                    $request->get('session_id');
        
        if (!$sessionId) {
            return;
        }

        // Buscar tarefas da sessão
        $sessionTasks = \App\Models\Tarefa::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        if ($sessionTasks->isEmpty()) {
            return;
        }

        $migrated = 0;
        $removed = 0;

        foreach ($sessionTasks as $sessionTask) {
            // Verificar se o usuário já tem uma tarefa similar
            $existingTask = \App\Models\Tarefa::where('user_id', $userId)
                ->where('titulo', $sessionTask->titulo)
                ->where('descricao', $sessionTask->descricao ?? '')
                ->first();

            if ($existingTask) {
                // Remove tarefa de sessão duplicada
                $sessionTask->delete();
                $removed++;
            } else {
                // Migra tarefa para o usuário
                $sessionTask->update([
                    'user_id' => $userId,
                    'session_id' => null
                ]);
                $migrated++;
            }
        }

        // Log da migração (opcional)
        Log::info("Migração de tarefas - Usuário {$userId}: {$migrated} migradas, {$removed} removidas");
    }
}
