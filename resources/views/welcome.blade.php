<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Aplicação web para gestão de tarefas com design acessível e inclusivo. Organize suas tarefas de forma eficiente e acessível.">
    <meta name="keywords" content="todolist, tarefas, gestão, acessibilidade, organização">
    <meta name="author" content="TodoList App">
    <title>TodoList - Gestão de Tarefas Acessível</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

</head>
<body>
    <!-- Skip Links -->
    <a href="#main-content" class="skip-link">
        Ir para o conteúdo principal
    </a>
    <a href="#navigation" class="skip-link">
        Ir para a navegação
    </a>
    
    <div id="app">
        <!-- Header -->
        <header role="banner" id="navigation">
            <nav role="navigation" aria-label="Navegação principal" class="navbar">
                <div class="navbar-content">
                    <div class="navbar-brand">
                        <a href="{{ route('home') }}" class="brand-link">
                            <img src="/images/lapistodolist.png" alt="TodoList" class="brand-icon">
                            <span class="brand-text">TodoList</span>
                        </a>
                    </div>
                    <div class="navbar-auth" id="navbar-auth">
                        <!-- Será preenchido pelo JavaScript -->
                    </div>
                </div>
            </nav>
        </header>

        <!-- Conteúdo -->
        <main id="main-content" role="main" tabindex="-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer role="contentinfo">
            
        </footer>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Estilos para o Navbar de Autenticação */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0.75rem 0;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand .brand-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: #2d3748;
            font-weight: 600;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
        }

        .brand-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
        }

        .navbar-auth {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .auth-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .auth-btn-outline {
            background: transparent;
            color: #5a9fd4;
            border: 1px solid #5a9fd4;
        }

        .auth-btn-outline:hover {
            background: #87ceeb;
            color: #2d3748;
        }

        .auth-btn-solid {
            background: linear-gradient(135deg, #a8d0f0 0%, #87ceeb 100%);
            color: #2d3748;
        }

        .auth-btn-solid:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(168, 208, 240, 0.4);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #4a5568;
        }

        .user-name {
            font-weight: 500;
            color: #2d3748;
        }

        .logout-btn {
            background: #e53e3e;
            color: white;
        }

        .logout-btn:hover {
            background: #c53030;
        }

        @media (max-width: 768px) {
            .navbar-content {
                padding: 0 1rem;
            }
            
            .brand-text {
                display: none;
            }
            
            .auth-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
        }
    </style>

    <script>
        // Sistema de Autenticação Frontend
        document.addEventListener('DOMContentLoaded', function() {
            updateAuthNavbar();
            
            // Verificar se acabamos de fazer login
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('logged_in')) {
                // Remove o parâmetro da URL
                window.history.replaceState({}, document.title, window.location.pathname);
                // Força atualização da navbar
                setTimeout(updateAuthNavbar, 100);
            }
            
            // Escutar mudanças no localStorage (para quando login/logout acontecer em outras abas)
            window.addEventListener('storage', function(e) {
                if (e.key === 'auth_token' || e.key === 'user') {
                    updateAuthNavbar();
                }
            });
            
            // Força atualização da navbar após um pequeno delay
            setTimeout(updateAuthNavbar, 200);
        });

        function updateAuthNavbar() {
            const navbarAuth = document.getElementById('navbar-auth');
            const token = localStorage.getItem('auth_token');
            const user = JSON.parse(localStorage.getItem('user') || 'null');
            
            console.log('Auth state check:', { token: !!token, user: user });

            if (token && user) {
                // Usuário logado
                navbarAuth.innerHTML = `
                    <div class="user-info">
                        <span class="user-name">Olá, ${user.name}!</span>
                        <button onclick="logout()" class="auth-btn logout-btn">Sair</button>
                    </div>
                `;
            } else {
                // Usuário não logado
                navbarAuth.innerHTML = `
                    <a href="/login" class="auth-btn auth-btn-outline">Entrar</a>
                    <a href="/register" class="auth-btn auth-btn-solid">Criar Conta</a>
                `;
            }
        }

        async function logout() {
            try {
                const token = localStorage.getItem('auth_token');
                
                if (token) {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                }
            } catch (error) {
                console.log('Erro no logout:', error);
            } finally {
                // Limpar dados locais
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                
                // Atualizar navbar
                updateAuthNavbar();
                
                // Recarregar página se estiver na home (para atualizar as tarefas)
                if (window.location.pathname === '/') {
                    window.location.reload();
                }
            }
        }

        // Função global para ser usada pelos componentes Vue
        window.getAuthToken = function() {
            return localStorage.getItem('auth_token');
        };

        window.getAuthUser = function() {
            return JSON.parse(localStorage.getItem('user') || 'null');
        };

        window.updateAuthNavbar = updateAuthNavbar;
    </script>
    
    @stack('scripts')
</body>
</html>