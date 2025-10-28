<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
}
