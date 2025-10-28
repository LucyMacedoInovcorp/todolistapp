<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - TodoList</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
    <style>
        /* CSS para gradiente azul pastel */
        :root {
            --primary-gradient: linear-gradient(135deg, #a8d0f0 0%, #87ceeb 100%);
            --primary-hover: linear-gradient(135deg, #9bc7ed 0%, #7bc2e6 100%);
            --focus-shadow: 0 0 0 3px rgba(135, 206, 235, 0.3);
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
            padding: 20px;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-title {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #87ceeb;
            box-shadow: var(--focus-shadow);
        }

        .auth-button {
            width: 100%;
            padding: 14px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .auth-button:hover:not(:disabled) {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(135, 206, 235, 0.3);
        }

        .auth-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-link {
            color: #87ceeb;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Fazer Login</h1>
                <p class="auth-subtitle">Entre com sua conta para acessar suas tarefas</p>
            </div>

            <!-- Formulário tradicional com fallback -->
            <form id="login-form-traditional" method="POST" action="{{ url('/api/login') }}">
                @csrf
                <div class="error-message" id="general-error"></div>
                <div class="success-message" id="success-message"></div>

                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        required
                        autocomplete="username"
                    >
                    <div class="error-message" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        autocomplete="current-password"
                    >
                    <div class="error-message" id="password-error"></div>
                </div>

                <button type="submit" class="auth-button" id="login-button">
                    <span class="button-text">Entrar</span>
                    <span class="loading-spinner" id="loading-spinner"></span>
                </button>

                <div class="auth-links">
                    <p>
                        <a href="{{ route('register') }}" class="auth-link">Criar nova conta</a>
                    </p>
                    <p>
                        <a href="{{ route('home') }}" class="auth-link">Voltar para lista de tarefas</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form-traditional');
            const submitButton = document.getElementById('login-button');
            const buttonText = submitButton.querySelector('.button-text');
            const loadingSpinner = document.getElementById('loading-spinner');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Limpar mensagens anteriores
                document.querySelectorAll('.error-message').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
                
                // Mostrar loading
                submitButton.disabled = true;
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';

                try {
                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    
                    console.log('Iniciando login...');
                    console.log('CSRF Token:', !!csrfToken);
                    
                    // Método 1: Fetch com JSON
                    let response;
                    try {
                        console.log('Tentativa 1: JSON request');
                        response = await fetch('/api/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                email: formData.get('email'),
                                password: formData.get('password')
                            })
                        });
                        
                        console.log('JSON response status:', response.status);
                    } catch (fetchError) {
                        console.log('JSON request falhou, tentando FormData...', fetchError.message);
                        
                        // Método 2: FormData
                        response = await fetch('/api/login', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        
                        console.log('FormData response status:', response.status);
                    }

                    if (!response.ok) {
                        console.log('Response não OK, tentando submit tradicional...');
                        // Método 3: Submit tradicional do formulário
                        form.action = '/api/login';
                        form.submit();
                        return;
                    }

                    const result = await response.json();
                    console.log('Login result:', result);

                    if (result.token) {
                        localStorage.setItem('auth_token', result.token);
                        if (result.user) {
                            localStorage.setItem('user', JSON.stringify(result.user));
                        }
                        
                        const successMsg = document.getElementById('success-message');
                        successMsg.textContent = result.message || 'Login realizado com sucesso!';
                        successMsg.style.display = 'block';
                        
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 1000);
                    } else {
                        throw new Error('Token não recebido do servidor');
                    }

                } catch (error) {
                    console.error('Erro no login:', error);
                    
                    const generalError = document.getElementById('general-error');
                    generalError.textContent = 'Erro de conexão. Tentando método alternativo...';
                    generalError.style.display = 'block';
                    
                    // Fallback final: submit tradicional
                    setTimeout(() => {
                        form.action = '/api/login';
                        form.submit();
                    }, 2000);
                    
                } finally {
                    // Restaurar botão
                    submitButton.disabled = false;
                    buttonText.style.display = 'inline';
                    loadingSpinner.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>