<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro - TodoList</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="pagina-principal">
        <div class="todolist-container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Criar Nova Conta</h1>
                        <p>Gerencie suas tarefas com sua conta pessoal</p>
                    </div>

                    <form id="register-form" class="auth-form">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-input" 
                                required 
                                autocomplete="name"
                                placeholder="Seu nome completo"
                            >
                            <div class="error-message" id="name-error"></div>
                        </div>

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
                                autocomplete="new-password"
                                placeholder="Mínimo 8 caracteres"
                                minlength="8"
                            >
                            <div class="error-message" id="password-error"></div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="form-input" 
                                required 
                                autocomplete="new-password"
                                placeholder="Digite a senha novamente"
                                minlength="8"
                            >
                            <div class="error-message" id="password_confirmation-error"></div>
                        </div>

                        <button type="submit" class="auth-button auth-button-primary">
                            <span class="button-text">Criar Conta</span>
                            <span class="loading-spinner" style="display: none;">🔄</span>
                        </button>

                        <div class="auth-links">
                            <p>
                                Já tem uma conta? 
                                <a href="{{ route('login') }}" class="auth-link">Fazer login</a>
                            </p>
                            <p>
                                <a href="{{ route('home') }}" class="auth-link">Voltar para lista de tarefas</a>
                            </p>
                        </div>

                        <div class="auth-note">
                            <p><strong>Vantagens de criar conta:</strong></p>
                            <ul>
                                <li>Suas tarefas ficam salvas permanentemente</li>
                                <li>Acesse de qualquer dispositivo</li>
                                <li>Não perca seus dados ao limpar o navegador</li>
                            </ul>
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
            document.getElementById('register-form').addEventListener('submit', async function(e) {
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
                
                // Validação de senha
                const password = form.querySelector('#password').value;
                const passwordConfirmation = form.querySelector('#password_confirmation').value;
                
                if (password !== passwordConfirmation) {
                    const errorElement = document.getElementById('password_confirmation-error');
                    errorElement.textContent = 'As senhas não coincidem';
                    errorElement.style.display = 'block';
                    return;
                }
                
                document.getElementById('success-message').style.display = 'none';
                document.getElementById('general-error').style.display = 'none';

                // Mostrar loading
                submitButton.disabled = true;
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';

                try {
                    const formData = new FormData(form);
                    
                    // Tentar diferentes URLs para debug
                    let registerUrl = '/api/register';
                    
                    console.log('Base URL:', window.location.origin);
                    console.log('Current URL:', window.location.href);
                    console.log('Attempting register to:', registerUrl);
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    console.log('CSRF Token found:', !!csrfToken);
                    
                    if (!csrfToken) {
                        throw new Error('CSRF token não encontrado');
                    }
                    
                    const requestBody = {
                        name: formData.get('name'),
                        email: formData.get('email'),
                        password: formData.get('password'),
                        password_confirmation: formData.get('password_confirmation')
                    };
                    
                    console.log('Request body:', requestBody);
                    
                    const response = await fetch(registerUrl, {
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
                    console.log('Response ok:', response.ok);
                    console.log('Response headers:', Object.fromEntries(response.headers.entries()));
                    
                    if (!response.ok) {
                        console.log('Request failed, trying fallback method...');
                        
                        // Fallback: usar FormData diretamente
                        const fallbackResponse = await fetch(registerUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        
                        console.log('Fallback response status:', fallbackResponse.status);
                        console.log('Fallback response ok:', fallbackResponse.ok);
                        
                        if (!fallbackResponse.ok) {
                            const errorText = await fallbackResponse.text();
                            console.error('Fallback error text:', errorText);
                            
                            try {
                                const errorJson = JSON.parse(errorText);
                                if (errorJson.errors) {
                                    Object.keys(errorJson.errors).forEach(field => {
                                        const errorElement = document.getElementById(field + '-error');
                                        if (errorElement) {
                                            errorElement.textContent = errorJson.errors[field][0];
                                            errorElement.style.display = 'block';
                                        }
                                    });
                                    return;
                                }
                            } catch (parseError) {
                                console.log('Could not parse error as JSON:', parseError);
                            }
                            
                            throw new Error(`Erro de conexão (${fallbackResponse.status}): ${errorText}`);
                        }
                        
                        const result = await fallbackResponse.json();
                        
                        if (result.token) {
                            localStorage.setItem('auth_token', result.token);
                            localStorage.setItem('user', JSON.stringify(result.user));
                            
                            const successMsg = document.getElementById('success-message');
                            successMsg.textContent = result.message || 'Conta criada com sucesso!';
                            successMsg.style.display = 'block';
                            
                            setTimeout(() => {
                                window.location.href = '/';
                            }, 1000);
                        } else {
                            throw new Error('Token não recebido do servidor');
                        }
                        return;
                    }

                    const data = await response.json();
                    console.log('Response data:', data);

                    if (response.ok) {
                        // Registro bem-sucedido
                        localStorage.setItem('auth_token', data.token);
                        localStorage.setItem('user', JSON.stringify(data.user));
                        
                        const successMsg = document.getElementById('success-message');
                        successMsg.textContent = data.message;
                        successMsg.style.display = 'block';
                        
                        // Redirecionar após 1 segundo
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 1000);
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
                            generalError.textContent = data.message || 'Erro ao criar conta';
                            generalError.style.display = 'block';
                        }
                    }
                } catch (error) {
                    console.error('Register error:', error);
                    console.error('Error stack:', error.stack);
                    
                    const generalError = document.getElementById('general-error');
                    
                    let errorMessage = 'Erro de conexão. Tente novamente.';
                    
                    if (error.message.includes('Failed to fetch')) {
                        errorMessage = 'Erro de rede. Verifique sua conexão.';
                    } else if (error.message.includes('CSRF token')) {
                        errorMessage = 'Erro de segurança. Recarregue a página.';
                    } else if (error.message.includes('Token não recebido')) {
                        errorMessage = error.message;
                    }
                    
                    generalError.textContent = errorMessage;
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