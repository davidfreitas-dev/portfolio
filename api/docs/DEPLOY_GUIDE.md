# Guia Profissional: Deploy da Aplicação Portfolio (API, Site e CMS)

## Índice

- [1. Preparação da Estrutura (Pastas e Permissões)](#1-preparação-da-estrutura-pastas-e-permissões)
- [2. Configuração do PHP-FPM (Isolamento da API)](#2-configuração-do-php-fpm-isolamento-da-api)
- [3. Servidor Web (Nginx)](#3-servidor-web-nginx)
- [4. Segurança com SSL (HTTPS) e Cloudflare](#4-segurança-com-ssl-https-e-cloudflare)
- [5. Automação de Deploy (GitHub Actions)](#5-automação-de-deploy-github-actions)

---

## 1. Preparação da Estrutura (Pastas e Permissões)

### 1.1 Criar estrutura de pastas
```bash
sudo mkdir -p /var/www/portfolio/{api,site,cms}
sudo mkdir -p /var/www/portfolio/api/public
sudo mkdir -p /var/www/portfolio/api/logs

# Garantir proprietário (Substitua $USER pelo seu usuário se necessário)
sudo chown -R $USER:www-data /var/www/portfolio
```

### 1.2 Ajuste Fino de Permissões (Evita Erro 403 e falhas no SSL)
O Nginx e o Certbot precisam de permissões específicas para acessar os arquivos e validar desafios:

```bash
# Permissões para pastas (775) e arquivos (664)
sudo find /var/www/portfolio -type d -exec chmod 775 {} +
sudo find /var/www/portfolio -type f -exec chmod 664 {} +

# Garantir permissão de execução nos diretórios pai (Essencial para o Nginx)
sudo chmod +x /var/www
sudo chmod +x /var/www/portfolio
```

---

## 2. Configuração do PHP-FPM (Isolamento da API)

Cada aplicação deve rodar em seu próprio processo por segurança.

### 2.1 Criar Pool PHP-FPM Dedicado
```bash
sudo nano /etc/php/8.4/fpm/pool.d/portfolio-api.conf
```

**Configuração:**
```ini
[portfolio-api]
user = www-data
group = www-data
listen = /run/php/php8.4-fpm-portfolio-api.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500

php_admin_value[error_log] = /var/www/portfolio/api/logs/php-fpm.error.log
php_admin_flag[log_errors] = on
```

Reinicie o PHP para carregar:
```bash
sudo systemctl restart php8.4-fpm
```

---

## 3. Servidor Web (Nginx)

### 3.1 Criar o arquivo de configuração
```bash
sudo nano /etc/nginx/sites-available/portfolio
```

**Conteúdo do Arquivo:**

> **Nota importante sobre Headers:** No Nginx, se você usar `add_header` dentro de um bloco `location`, ele ignora os headers definidos no bloco `server`. Por isso, os headers de segurança devem ser repetidos em locais que possuem seu próprio `add_header` (como cache de estáticos).

```nginx
# 1. SITE PRINCIPAL - Redirect HTTP → HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

# 1. SITE PRINCIPAL - HTTPS
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/portfolio/site;
    index index.html;

    # SSL config here

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    # CSP removido temporariamente para mapeamento de domínios externos

    error_log /var/www/portfolio/site/error.log;

    # Bloquear dotfiles (.env, .git, .htaccess, etc.)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Bloquear arquivos sensíveis
    location ~* \.(env|log|sql|yaml|yml|json)$ {
        deny all;
    }

    location / {
        try_files $uri $uri/ =404;
    }

    # Assets Estáticos com Cache e Headers de Segurança repetidos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|webp)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        
        # Repetir headers de segurança (Obrigatório pois add_header limpa herança)
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header Referrer-Policy "no-referrer-when-downgrade" always;
    }
}

# 2. API - Redirect HTTP → HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name api.yourdomain.com;
    return 301 https://$host$request_uri;
}

# 2. API - HTTPS
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name api.yourdomain.com;
    root /var/www/portfolio/api/public;
    index index.php;

    # SSL config here

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    # CSP removido temporariamente

    error_log /var/www/portfolio/api/logs/error.log;

    # Bloquear dotfiles (.env, .git, .htaccess, etc.)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Bloquear arquivos sensíveis
    location ~* \.(env|log|sql|yaml|yml)$ {
        deny all;
    }

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location /internal_static/ {
        internal;
        alias /var/www/portfolio/api/storage/;
    }

    location /internal_uploads/ {
        internal;
        alias /var/www/portfolio/api/storage/uploads/;
    }

    location ~ \.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.4-fpm-portfolio-api.sock;
        
        # Repetir headers de segurança
        add_header X-Frame-Options "SAMEORIGIN" always;
        add_header X-XSS-Protection "1; mode=block" always;
        add_header X-Content-Type-Options "nosniff" always;
        add_header Referrer-Policy "no-referrer-when-downgrade" always;
    }
}

# 3. CMS - Redirect HTTP → HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name cms.yourdomain.com;
    return 301 https://$host$request_uri;
}

# 3. CMS - HTTPS
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name cms.yourdomain.com;
    root /var/www/portfolio/cms;
    index index.html;

    # SSL config here

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    # CSP removido temporariamente

    error_log /var/www/portfolio/cms/error.log;

    # Bloquear dotfiles (.env, .git, .htaccess, etc.)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Bloquear arquivos sensíveis
    location ~* \.(env|log|sql|yaml|yml|json)$ {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

### 3.2 Ativar e Testar
```bash
sudo ln -s /etc/nginx/sites-available/portfolio /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

---

## 4. Segurança com SSL (HTTPS) e Cloudflare

Para que a geração do certificado funcione sem erros, siga este checklist:

### 4.1 Checklist Pré-SSL (Obrigatório)
1.  **DNS:** Garanta que os domínios apontam para o IP da VPS.
2.  **Cloudflare:** Mude o status para **DNS Only (Nuvem Cinza)** temporariamente. O Proxy (Nuvem Laranja) bloqueia a validação se o certificado ainda não existir na origem.
3.  **Permissões:** Garanta que rodou o passo **1.2** para evitar erros de acesso.

### 4.2 Gerar Certificado com Certbot
```bash
sudo certbot --nginx -d seu-dominio.com.br -d www.seu-dominio.com.br -d api.seu-dominio.com.br -d cms.seu-dominio.com.br
```

### 4.3 Ativar Proxy e SSL Estrito
Após o SSL ser instalado com sucesso na VPS, você deve reativar a segurança do Cloudflare.
1.  No Cloudflare, mude os registros de volta para **Proxied (Nuvem Laranja)**.
2.  Em **SSL/TLS**, altere o modo para **Full (Strict)**.

### 4.4 Trusted Proxies (.env da API)
Adicione as faixas de IP do Cloudflare ao seu arquivo `.env` para que a aplicação identifique corretamente o IP real dos usuários:
```env
TRUSTED_PROXIES=103.21.244.0/22,103.22.200.0/22,103.31.4.0/22,104.16.0.0/13,104.24.0.0/14,108.162.192.0/18,131.0.72.0/22,141.101.64.0/18,162.158.0.0/15,172.64.0.0/13,173.245.48.0/20,188.114.96.0/20,190.93.240.0/20,197.234.240.0/22,198.41.128.0/17
```

---

## 5. Automação de Deploy (GitHub Actions)

### 5.1. Gerar Par de Chaves SSH no Servidor

Conecte-se ao servidor e execute:

```bash
# Acessar diretório SSH do usuário
cd ~/.ssh

# Gerar chave SSH dedicada para GitHub Actions
ssh-keygen -t ed25519 -C "github-actions-deploy@site.example.com" -f github-actions

# Quando solicitado:
# - Enter file in which to save the key: [já preenchido com github-actions]
# - Enter passphrase: [deixe vazio - apenas pressione Enter]
# - Enter same passphrase again: [deixe vazio - pressione Enter novamente]
```

**Por que usar ed25519?**
- Mais seguro e moderno que RSA
- Chaves menores (mais fácil de gerenciar)
- Melhor performance

### 5.2. Adicionar Chave Pública ao `authorized_keys`

```bash
# Adicionar chave pública ao arquivo authorized_keys
cat ~/.ssh/github-actions.pub >> ~/.ssh/authorized_keys

# Ajustar permissões (importante para segurança SSH)
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### 5.3. Copiar Chave Privada para o GitHub

```bash
# Exibir chave privada
cat ~/.ssh/github-actions
```

**Copie TODO o conteúdo**, incluindo as linhas:
```
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

### 5.4. Configurar Secrets no GitHub

No GitHub, acesse **Settings** → **Secrets and variables** → **Actions** e adicione os seguintes segredos:

| Nome | Valor |
| :--- | :--- |
| `SSH_PRIVATE_KEY` | Conteúdo completo da chave privada (passo 5.3) |
| `REMOTE_HOST` | IP do seu servidor |
| `REMOTE_USER` | Seu usuário SSH (ex: `ubuntu` ou `root`) |
| `REMOTE_PORT` | `22` |
| `REMOTE_TARGET` | Caminho base (ex: `/var/www/portfolio`) |