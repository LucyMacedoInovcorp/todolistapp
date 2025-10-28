<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - TodoList</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="pagina-principal">
        <div class="todolist-container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Entrar na sua Conta</h1>
                        <p>Acesse suas tarefas pessoais</p>
                    </div>

                    <form id="login-form" class="auth-form">
                        @csrf
                        <div class="form-group">
                            <label for="email" class="form-label">E-mail</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                required 
                                autocomplete="email"
                                placeholder="seu@email.com"
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
                                placeholder="Digite sua senha"
                            >
                            <div class="error-message" id="password-error"></div>
                        </div>

                        <button type="submit" class="auth-button auth-button-primary">
                            <span class="button-text">Entrar</span>
                            <span class="loading-spinner" style="display: none;">🔄</span>
                        </button>

                        <div class="auth-links">
                            <p>
                                Não tem uma conta? 
                                <a href="{{ route('register') }}" class="auth-link">Criar conta</a>
                            </p>
                            <p>
                                <a href="{{ route('home') }}" class="auth-link">Voltar para lista de tarefas</a>
                            </p>
                        </div>

                        <div class="auth-note">
                            <p><strong>Nota:</strong> Você também pode usar a aplicação sem criar conta. Suas tarefas serão salvas na sua sessão do navegador.</p>
                        </div>
                    </form>

                    <div class="success-message" id="success-message" style="display: none;"></div>
                    <div class="error-message" id="general-error" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('login-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const form = e.target;
                const submitButton = form.querySelector('.auth-button');
                const buttonText = submitButton.querySelector('.button-text');
                const loadingSpinner = submitButton.querySelector('.loading-spinner');
                
                // Limpar mensagens de erro anteriores
                document.querySelectorAll('.error-message').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
                document.getElementById('success-message').style.display = 'none';
                document.getElementById('general-error').style.display = 'none';

                // Mostrar loading
                submitButton.disabled = true;
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';

                try {
                    const formData = new FormData(form);
                    
                    // Tentar diferentes URLs para debug
                    let loginUrl = '/api/login';
                    
                    console.log('Base URL:', window.location.origin);
                    console.log('Current URL:', window.location.href);
                    console.log('Attempting login to:', loginUrl);
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    console.log('CSRF Token found:', !!csrfToken);
                    
                    if (!csrfToken) {
                        throw new Error('CSRF token não encontrado');
                    }
                    
                    const requestBody = {
                        email: formData.get('email'),
                        password: formData.get('password')
                    };
                    
                    console.log('Request body:', requestBody);
                    
                    const response = await fetch(loginUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(requestBody)
                    });

                    console.log('Response status:', response.status);
                    
                    if (!response.ok && response.status >= 500) {
                        throw new Error(`Erro do servidor (${response.status}): ${response.statusText}`);
                    }

                    const data = await response.json();
                    console.log('Response data:', data);

                    if (response.ok) {
                        // Login bem-sucedido
                        console.log('Login successful:', data);
                        localStorage.setItem('auth_token', data.token);
                        localStorage.setItem('user', JSON.stringify(data.user));
                        
                        const successMsg = document.getElementById('success-message');
                        successMsg.textContent = data.message || 'Login realizado com sucesso!';
                        successMsg.style.display = 'block';
                        
                        // Redirecionar após 1.5 segundos para garantir que o localStorage seja atualizado
                        setTimeout(() => {
                            console.log('Redirecting to home...');
                            // Tentar atualizar a navbar na página de destino
                            window.location.href = '/?logged_in=1';
                        }, 1500);
                    } else {
                        // Erro de validação
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const errorElement = document.getElementById(field + '-error');
                                if (errorElement) {
                                    errorElement.textContent = data.errors[field][0];
                                    errorElement.style.display = 'block';
                                }
                            });
                        } else {
                            const generalError = document.getElementById('general-error');
                            generalError.textContent = data.message || 'Erro ao fazer login';
                            generalError.style.display = 'block';
                        }
                    }
                } catch (error) {
                    console.error('Login error (JSON method):', error);
                    
                    // Fallback: tentar com form-data
                    try {
                        console.log('Trying fallback with form-data...');
                        const formData = new FormData(form);
                        
                        const response = await fetch('/api/login', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        
                        console.log('Fallback response status:', response.status);
                        
                        if (!response.ok && response.status >= 500) {
                            throw new Error(`Erro do servidor (${response.status}): ${response.statusText}`);
                        }

                        const data = await response.json();
                        console.log('Fallback response data:', data);

                        if (response.ok) {
                            // Login bem-sucedido
                            console.log('Login successful with fallback:', data);
                            localStorage.setItem('auth_token', data.token);
                            localStorage.setItem('user', JSON.stringify(data.user));
                            
                            const successMsg = document.getElementById('success-message');
                            successMsg.textContent = data.message || 'Login realizado com sucesso!';
                            successMsg.style.display = 'block';
                            
                            // Redirecionar após 1.5 segundos para garantir que o localStorage seja atualizado
                            setTimeout(() => {
                                console.log('Redirecting to home...');
                                // Tentar atualizar a navbar na página de destino
                                window.location.href = '/?logged_in=1';
                            }, 1500);
                            return; // Sucesso com fallback
                        } else {
                            // Processar erros do fallback
                            if (data.errors) {
                                Object.keys(data.errors).forEach(field => {
                                    const errorElement = document.getElementById(field + '-error');
                                    if (errorElement) {
                                        errorElement.textContent = data.errors[field][0];
                                        errorElement.style.display = 'block';
                                    }
                                });
                                return;
                            }
                        }
                    } catch (fallbackError) {
                        console.error('Fallback error:', fallbackError);
                    }
                    
                    // Se chegou até aqui, todos os métodos falharam
                    const generalError = document.getElementById('general-error');
                    generalError.textContent = `Erro de conexão: ${error.message || 'Verifique sua conexão e tente novamente.'}`;
                    generalError.style.display = 'block';
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