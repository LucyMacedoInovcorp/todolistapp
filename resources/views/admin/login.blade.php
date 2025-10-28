<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso à Administração - TodoList</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #333;
            margin: 0;
        }
        .login-header p {
            color: #666;
            margin: 0.5rem 0 0 0;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-1px);
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .info-box h3 {
            margin: 0 0 0.5rem 0;
            color: #1976d2;
        }
        .info-box p {
            margin: 0;
            color: #424242;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Administração</h1>
            <p>Digite a chave de acesso para continuar</p>
        </div>
        
        <form method="GET" action="{{ $current_url }}">
            <div class="form-group">
                <label for="key">Chave de Acesso:</label>
                <input type="password" id="key" name="key" required placeholder="Digite a chave de acesso">
            </div>
            <button type="submit" class="btn">Acessar Painel</button>
        </form>
        
        <div class="info-box">
            <h3>📋 Como usar:</h3>
            <p><strong>Chave padrão:</strong> todolist2025</p>
            <p>Após o acesso, você poderá visualizar e exportar todos os dados da aplicação.</p>
        </div>
    </div>
</body>
</html>