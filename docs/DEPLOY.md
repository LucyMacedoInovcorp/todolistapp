# 🚀 Guia de Deploy - TodoList API

Este guia orienta sobre o processo de deploy da aplicação TodoList para produção.

## 🎉 Deploy Realizado com Laravel Cloud

**✅ Status:** Deploy concluído com sucesso!  
**🌐 URL de Produção:** https://todolistapp-main-sawgdc.laravel.cloud/  
**📅 Data do Deploy:** Outubro 27, 2025  
**🏗️ Plataforma:** Laravel Cloud

## 📋 Pré-Deploy Checklist

Antes de fazer o deploy, certifique-se de que:

- [ ] ✅ Todos os testes estão passando (`./vendor/bin/pest`)
- [ ] ✅ Código está commitado e no repositório
- [ ] ✅ Ambiente de produção preparado
- [ ] ✅ Banco de dados de produção configurado
- [ ] ✅ Domínio/DNS apontando para o servidor

## 🌐 Configurações de Produção

### 1. Arquivo .env de Produção

Crie/atualize o `.env` com configurações de produção:

```env
# Aplicação
APP_NAME="TodoList"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://todolistapp-main-sawgdc.laravel.cloud
APP_KEY=base64:sua_chave_de_producao

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=seu-servidor-bd
DB_PORT=3306
DB_DATABASE=todolist_production
DB_USERNAME=usuario_producao
DB_PASSWORD=senha_super_segura

# Cache e Performance
CACHE_STORE=redis  # ou database
SESSION_DRIVER=redis  # ou database
QUEUE_CONNECTION=database

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=error

# Segurança
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### 2. Comandos de Deploy

Execute na seguinte ordem:

```bash
# 1. Atualizar código
git pull origin main

# 2. Instalar dependências (produção)
composer install --optimize-autoloader --no-dev

# 3. Instalar dependências JavaScript
npm ci --production

# 4. Compilar assets
npm run build

# 5. Executar migrações
php artisan migrate --force

# 6. Otimizar aplicação
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Limpar caches antigos
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🔧 Configuração do Servidor Web

### Apache (.htaccess)

Certifique-se de que o `.htaccess` em `public/` está correto:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L]
</IfModule>
```

### Nginx

Configuração para Nginx:

```nginx
## 📝 Nota sobre Laravel Cloud

**O deploy foi realizado com Laravel Cloud, que gerencia automaticamente:**
- ✅ Configuração do servidor web (Nginx)
- ✅ Certificados SSL/HTTPS
- ✅ Otimizações de performance
- ✅ Monitoramento e logs
- ✅ Backup automático

**Configuração manual não necessária** - O Laravel Cloud cuida de toda a infraestrutura!
    root /caminho/para/todolist/public;

    ssl_certificate /caminho/para/certificado.crt;
    ssl_certificate_key /caminho/para/chave.key;

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

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## 📱 Atualizações da Documentação Pós-Deploy

### 1. Atualizar README.md

```markdown
# No README.md, substitua:
A aplicação estará disponível em `http://localhost:8000`

# Por:
A aplicação está disponível em `https://todolistapp-main-sawgdc.laravel.cloud`
```

### 2. Atualizar docs/INSTALACAO.md

```bash
# Substitua as URLs de teste:
curl http://localhost:8000
curl http://localhost:8000/api/tarefas

# Por:
curl https://todolistapp-main-sawgdc.laravel.cloud
curl https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas
```

### 3. Atualizar docs/API.md

```markdown
# Substitua a Base URL:
### Base URL
```
http://localhost:8000/api
```

# Por:
### Base URL
```
https://todolistapp-main-sawgdc.laravel.cloud/api
```
```

## 🔍 Verificação Pós-Deploy

### Testes Básicos

```bash
# 1. Testar se o site carrega
curl -I https://todolistapp-main-sawgdc.laravel.cloud
# Deve retornar: HTTP/1.1 200 OK

# 2. Testar API básica
curl https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas
# Deve retornar: [] ou lista de tarefas

# 3. Testar criação de tarefa
curl -X POST https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas \
  -H "Content-Type: application/json" \
  -d '{"titulo":"Teste Deploy","prioridade":"media"}'
```

### Verificações de Segurança

```bash
# 1. Verificar HTTPS
curl -I https://todolistapp-main-sawgdc.laravel.cloud
# Deve ter headers de segurança

# 2. Verificar redirecionamento HTTP → HTTPS
curl -I http://todolistapp-main-sawgdc.laravel.cloud
# Deve retornar: 301 Moved Permanently

# 3. Verificar se .env não está acessível
curl https://todolistapp-main-sawgdc.laravel.cloud/.env
# Deve retornar: 404 ou 403
```

## 🚨 Troubleshooting Pós-Deploy

### Problemas Comuns

**Erro 500 - Internal Server Error:**
```bash
# Verificar logs
tail -f /caminho/para/logs/laravel.log

# Verificar permissões
chmod -R 775 storage bootstrap/cache
```

**Assets não carregam (CSS/JS):**
```bash
# Recompilar assets
npm run build

# Verificar se Vite gerou os arquivos
ls -la public/build/
```

**Banco de dados não conecta:**
```bash
# Testar conexão
php artisan tinker
> \DB::connection()->getPdo();
```

## 🔄 Script de Deploy Automatizado

Crie um script `deploy.sh` para automatizar:

```bash
#!/bin/bash

echo "🚀 Iniciando deploy..."

# Backup do banco (opcional)
# mysqldump todolist_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Atualizar código
git pull origin main

# Dependências PHP
composer install --optimize-autoloader --no-dev

# Dependências JS
npm ci --production

# Compilar assets
npm run build

# Migrações
php artisan migrate --force

# Otimizações
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reiniciar serviços (se necessário)
# sudo systemctl reload php8.2-fpm
# sudo systemctl reload nginx

echo "✅ Deploy concluído!"

# Testar se está funcionando
curl -f https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas > /dev/null && echo "✅ API funcionando" || echo "❌ API com problema"
```

## 📋 Template de Checklist Pós-Deploy

Copie e cole este checklist após cada deploy:

```markdown
## Deploy - $(date)

### ✅ Checklist Técnico
- [ ] Código atualizado (git pull)
- [ ] Dependências instaladas
- [ ] Assets compilados
- [ ] Migrações executadas
- [ ] Caches otimizados
- [ ] Permissões corretas

### ✅ Checklist Funcional  
- [ ] Site carrega (https://todolistapp-main-sawgdc.laravel.cloud)
- [ ] API responde (https://todolistapp-main-sawgdc.laravel.cloud/api/tarefas)
- [ ] Criação de tarefa funciona
- [ ] Listagem funciona
- [ ] Filtros funcionam
- [ ] Interface Vue.js carrega

### ✅ Checklist de Segurança
- [ ] HTTPS funcionando
- [ ] Headers de segurança presentes
- [ ] .env não acessível
- [ ] Logs configurados

### 🔗 URLs Atualizadas
- **Site**: https://todolistapp-main-sawgdc.laravel.cloud
- **API**: https://todolistapp-main-sawgdc.laravel.cloud/api
- **Docs**: Atualizadas com novas URLs
```

---

