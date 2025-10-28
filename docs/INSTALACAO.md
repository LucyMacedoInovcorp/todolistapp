# 🚀 Guia de Instalação e Configuração

Este guia irá te ajudar a configurar o projeto TodoList API do zero em seu ambiente local.

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter os seguintes softwares instalados:

### Obrigatórios
- **PHP 8.2+** - [Download](https://www.php.net/downloads.php)
- **Composer** - [Download](https://getcomposer.org/download/)
- **Node.js 18+** - [Download](https://nodejs.org/download/)
- **npm ou Yarn** - Incluído com Node.js
- **MySQL 8.0+** - [Download](https://dev.mysql.com/downloads/mysql/)
- **Git** - [Download](https://git-scm.com/downloads)

### Extensões PHP Necessárias
```bash
# No Windows, certifique-se de que essas extensões estão habilitadas no php.ini:
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=tokenizer
extension=xml
extension=ctype
extension=json
extension=bcmath
```

### Verificação dos Pré-requisitos

```bash
# Verificar versão do PHP
php --version

# Verificar se o Composer está instalado
composer --version

# Verificar versão do Node.js
node --version

# Verificar versão do npm
npm --version

# Verificar se o MySQL está rodando
mysql --version

# Verificar se o Git está instalado
git --version
```

## 📥 Instalação Passo a Passo

### 1. Clone o Repositório

```bash
# Clone o projeto
git clone <url-do-seu-repositorio> todolist
cd todolist

# Ou se você fez download do ZIP
unzip todolist.zip
cd todolist
```

### 2. Instalar Dependências

```bash
# Instalar dependências do PHP via Composer
composer install

# Instalar dependências JavaScript via npm
npm install

# Se estiver em produção, use:
composer install --optimize-autoloader --no-dev
npm ci --production
```

### 3. Configurar Ambiente

```bash
# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate
```

### 4. Configurar Banco de Dados

#### 4.1 Criar Bancos de Dados

```sql
# Entre no MySQL como root
mysql -u root -p

# Criar banco de produção
CREATE DATABASE todolist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Criar banco de testes
CREATE DATABASE todolist_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Criar usuário (opcional, mas recomendado)
CREATE USER 'todolist_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON todolist.* TO 'todolist_user'@'localhost';
GRANT ALL PRIVILEGES ON todolist_test.* TO 'todolist_user'@'localhost';
FLUSH PRIVILEGES;

# Sair do MySQL
EXIT;
```

#### 4.2 Configurar Arquivo .env

Edite o arquivo `.env` com suas configurações de banco:

```env
# Configurações da aplicação
APP_NAME="TodoList API"
APP_ENV=local
APP_KEY=base64:sua_chave_aqui
APP_DEBUG=true
APP_URL=http://localhost

# Configurações do banco de dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todolist
DB_USERNAME=todolist_user
DB_PASSWORD=senha_segura

# Cache e sessão
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Email (para desenvolvimento)
MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@todolist.local"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Executar Migrações

```bash
# Executar migrações no banco principal
php artisan migrate

# Executar migrações no banco de teste
php artisan migrate --env=testing
```

### 6. Configurar Permissões (Linux/Mac)

```bash
# Dar permissões para os diretórios de storage e bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Definir o usuário correto (substitua 'www-data' pelo seu servidor web)
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 7. Compilar Assets do Frontend

```bash
# Compilação para desenvolvimento (com watch mode)
npm run dev

# Ou compilação para produção
npm run build
```

### 8. Testar a Instalação

#### Ambiente de Desenvolvimento

```bash
# Para desenvolvimento local
php artisan serve
# O servidor local estará disponível em: http://localhost:8000

# Para produção (Laravel Cloud)
# A aplicação está disponível em: https://todolistapp-main-sawgdc.laravel.cloud
```

Teste se a aplicação está funcionando:
```bash
# Testar interface web em produção
curl https://todolistapp-main-sawgdc.laravel.cloud

# Testar endpoint da API em produção
curl https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas

# Para desenvolvimento local:
# curl http://localhost:8000
# curl http://localhost:8000/api/tarefas
```

#### URLs da Aplicação

**🌐 Produção (Laravel Cloud):**

```bash
# Produção já configurada no Laravel Cloud
# Testar interface web
curl https://todolistapp-main-sawgdc.laravel.cloud

# Testar endpoint da API  
curl https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas
```

**✅ Deploy Concluído**: 
- ✅ URL real: https://todolistapp-main-sawgdc.laravel.cloud
- ✅ Laravel Cloud gerencia toda a infraestrutura
- ✅ SSL/HTTPS configurado automaticamente

### 8. Executar Testes (Opcional)

```bash
# Executar todos os testes
./vendor/bin/pest

# Executar testes com relatório
./vendor/bin/pest --coverage
```

## 🔧 Configurações Adicionais

### Configuração para Produção

Se você está configurando para produção:

1. **Configure o .env para produção:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://todolistapp-main-sawgdc.laravel.cloud
LOG_LEVEL=error
```

2. **Compile assets para produção:**
```bash
# Compilar assets otimizados
npm run build

# Limpar cache de desenvolvimento
npm run clean # se disponível
```

3. **Otimize a aplicação:**
```bash
# Cache de configuração
php artisan config:cache

# Cache de rotas
php artisan route:cache

# Cache de views
php artisan view:cache

# Otimizar autoloader
composer install --optimize-autoloader --no-dev
```

4. **Configurações de segurança:**
```env
# Adicione no .env de produção
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SANCTUM_STATEFUL_DOMAINS=todolistapp-main-sawgdc.laravel.cloud
```

### Configuração de Servidor Web

#### Apache (.htaccess)

O Laravel já inclui um arquivo `.htaccess` em `public/`. Certifique-se de que o módulo `mod_rewrite` está habilitado.

#### Nginx

```nginx
server {
    listen 80;
    server_name todolist.local;
    root /caminho/para/todolist/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🚨 Resolução de Problemas Comuns

### Erro: "Class not found"
```bash
# Regenerar autoload
composer dump-autoload
```

### Erro: "No application encryption key"
```bash
# Gerar nova chave
php artisan key:generate
```

### Erro de Permissão (Linux/Mac)
```bash
# Corrigir permissões
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Erro de Conexão com Banco
1. Verifique se o MySQL está rodando
2. Confirme as credenciais no `.env`
3. Teste a conexão manualmente:
```bash
mysql -h 127.0.0.1 -u todolist_user -p todolist
```

### Erro "Port already in use"
```bash
# Usar porta diferente
php artisan serve --port=8080
```

## 🔍 Verificação Final

### Checklist para Desenvolvimento Local

Execute esta checklist para confirmar que tudo está funcionando localmente:

- [ ] ✅ Servidor inicia sem erros (`php artisan serve`)
- [ ] ✅ Página inicial carrega (`http://localhost:8000`)
- [ ] ✅ API responde (`curl http://localhost:8000/api/tarefas`)
- [ ] ✅ Testes passam (`./vendor/bin/pest`)
- [ ] ✅ Migrações executadas (`php artisan migrate:status`)
- [ ] ✅ Assets compilados (`npm run dev` funciona)

### Checklist para Produção (Laravel Cloud)

Verificação da aplicação em produção:

- [x] ✅ Aplicação deployada com sucesso
- [x] ✅ Interface web carrega (`https://todolistapp-main-sawgdc.laravel.cloud`)
- [x] ✅ API responde (`https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas`)
- [x] ✅ HTTPS habilitado e funcionando
- [x] ✅ Base de dados configurada
- [x] ✅ Assets compilados e servidos
- [ ] ✅ Interface Vue.js carrega corretamente

### URLs Atuais

**🌐 Produção:** https://todolistapp-main-sawgdc.laravel.cloud  
**� API:** https://todolistapp-main-sawgdc.laravel.cloud/api  
**� Desenvolvimento:** http://localhost:8000 (quando executando localmente)
- [ ] 🎨 Assets compilados e servidos corretamente
- [ ] 🔧 Caches otimizados (config, routes, views)
- [ ] 📝 Logs configurados adequadamente
- [ ] 🧪 Testes executados no ambiente de produção
- [ ] 🚀 Performance e tempo de resposta aceitáveis

**� Deploy Concluído!**

```bash
# URLs Atualizadas:
# Desenvolvimento: http://localhost:8000
# Produção: https://todolistapp-main-sawgdc.laravel.cloud

# Testar em produção:
curl https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas
curl https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas/1
```

## 📞 Suporte

Se você encontrar problemas durante a instalação:

1. Verifique se todos os pré-requisitos estão instalados
2. Confirme as permissões de arquivo
3. Verifique os logs em `storage/logs/laravel.log`
4. Consulte a [documentação do Laravel](https://laravel.com/docs)

## 🔄 Atualizações

Para atualizar o projeto:

```bash
# Baixar alterações
git pull origin main

# Atualizar dependências
composer install

# Executar novas migrações
php artisan migrate

# Limpar caches (se necessário)
php artisan config:clear
php artisan route:clear
php artisan view:clear
```



