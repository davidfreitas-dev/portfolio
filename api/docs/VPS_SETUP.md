# Guia Profissional: Configuração de VPS (Slim PHP, Nginx, MariaDB, Redis)

## Índice

- [Especificações do Servidor](#especificações-do-servidor)
- [0. Preparação do Ambiente (Obrigatório)](#0-preparação-do-ambiente-obrigatório)
- [1. Configuração Inicial do Sistema](#1-configuração-inicial-do-sistema)
- [2. Instalando o PHP 8.4](#2-instalando-o-php-84)
- [3. Instalando o Nginx 1.24](#3-instalando-o-nginx-124)
- [4. Instalando MariaDB 10.11](#4-instalando-mariadb-1011)
- [5. Instalando e Configurando Redis](#5-instalando-e-configurando-redis)
- [6. Instalando Composer e ACL](#6-instalando-composer-e-acl)
- [7. Estrutura de Diretórios](#7-estrutura-de-diretórios)
- [8. Segurança e Firewall](#8-segurança-e-firewall)
- [9. Endurecimento do SSH](#9-endurecimento-do-ssh)
- [10. Monitoramento e Comandos Úteis](#10-monitoramento-e-comandos-úteis)
- [11. Manutenção e Limpeza](#11-manutenção-e-limpeza)

---

## Especificações do Servidor

| Componente | Versão/Configuração |
|------------|---------------------|
| **SO** | Ubuntu 22.04 LTS |
| **RAM** | 1GB + 2GB SWAP |
| **CPU** | 1 OCPU |
| **Disco** | 50GB |
| **Web Server** | Nginx 1.24 |
| **Banco de Dados** | MariaDB 10.11 |
| **Cache** | Redis 7.x |
| **PHP** | 8.4 |

---

## 0. Preparação do Ambiente (Obrigatório)

**Nunca execute todas as configurações como `root`**.

### 0.1 Criando Usuário (Obrigatório)

Crie um usuário com privilégios de administrador:

```bash
# 1. Criar novo usuário (substitua 'seu-usuario' pelo nome desejado)
adduser seu-usuario

# 2. Adicionar ao grupo sudo
usermod -aG sudo seu-usuario

# 3. Mudar para o novo usuário
su - seu-usuario

# 4. Se o comando retornar 'root', seu usuário está configurado corretamente.
sudo whoami
```

A partir daqui, utilize `sudo` antes de comandos que exigem privilégios elevados.

### 0.2 Configuração de Atalho SSH (Opcional - Local)

Para facilitar o acesso à sua VPS a partir da sua máquina local, configure um alias no seu arquivo `~/.ssh/config`:

1. Edite o arquivo na sua máquina local:
```bash
nano ~/.ssh/config
```

2. Adicione a configuração (substitua os campos entre `< >`):
```text
Host vps-projeto
    HostName <IP_DA_VPS>
    User seu-usuario
    IdentityFile ~/.ssh/id_rsa
```

Agora você pode acessar usando: `ssh vps-projeto`

---

## 1. Configuração Inicial do Sistema

### 1.1 Atualizar sistema e ferramentas básicas
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Verificar qual editor já está instalado no servidor
which nano vim vi

# Instalar editor de texto nano (se ainda não estiver instaladao)
sudo apt install -y nano

# Reiniciar o servidor
sudo reboot
```

### 1.2 Configurar SWAP (2GB)
```bash
# Criar arquivo de swap
sudo fallocate -l 2G /swapfile

# Ajustar permissões
sudo chmod 600 /swapfile

# Configurar como swap
sudo mkswap /swapfile

# Ativar swap
sudo swapon /swapfile

# Tornar permanente
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# Otimizar swappiness (usar swap apenas quando necessário)
sudo sysctl vm.swappiness=10
echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf

# Verificar
free -h
swapon --show
```

**Resultado esperado:**

```
               total        used        free      shared  buff/cache   available
Mem:           956Mi       200Mi       120Mi       1.0Mi       635Mi       610Mi
Swap:          2.0Gi          0B       2.0Gi
```

### 1.3 Configurar Atualizações Automáticas (unattended-upgrades)
Manter o sistema atualizado manualmente é arriscado para produção. Vamos configurar o `unattended-upgrades` para que patches críticos de segurança sejam aplicados automaticamente.

1.  **Instalar o pacote:**
    ```bash
    sudo apt install unattended-upgrades -y
    ```

2.  **Habilitar as atualizações automáticas:**
    ```bash
    sudo dpkg-reconfigure -plow unattended-upgrades
    ```
    *(Selecione **Yes** na tela que aparecer).*

3.  **Configurar reinicialização automática (Opcional):**
    Para que patches que exigem reboot (como kernel) sejam finalizados sozinhos durante a madrugada:
    ```bash
    sudo nano /etc/apt/apt.conf.d/50unattended-upgrades
    ```
    Localize e altere as linhas (remova o `//` se necessário):
    ```text
    Unattended-Upgrade::Automatic-Reboot "true";
    Unattended-Upgrade::Automatic-Reboot-Time "04:00";
    ```

4.  **Verificar se o serviço está rodando:**
    ```bash
    systemctl status unattended-upgrades
    ```

---

## 2. Instalando o PHP 8.4

### 2.1 Adicionar repositório Ondrej
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### 2.2 Instalar PHP 8.4 com extensões (incluindo Redis)
```bash
sudo apt install -y php8.4-fpm php8.4-cli php8.4-common php8.4-mysql \
php8.4-zip php8.4-gd php8.4-mbstring php8.4-curl php8.4-xml \
php8.4-bcmath php8.4-intl php8.4-opcache php8.4-redis
```

### 2.3 Verificar instalação
```bash
php -v
php-fpm8.4 -v

# Verificar se extensão Redis está instalada
php -m | grep redis
```

### 2.4 Configurar PHP-FPM para Múltiplos Pools
```bash
# Verificar se o serviço está pronto para gerenciar pools
ls /etc/php/8.4/fpm/pool.d/
```

### 2.5 Otimizar Configurações Gerais do PHP-FPM
```bash
sudo sed -i \
-e 's/^;*emergency_restart_threshold = .*/emergency_restart_threshold = 10/' \
-e 's/^;*emergency_restart_interval = .*/emergency_restart_interval = 1m/' \
-e 's/^;*process_control_timeout = .*/process_control_timeout = 10s/' \
/etc/php/8.4/fpm/php-fpm.conf
```

### 2.6 Otimizar PHP.ini
```bash
# Ajustar php.ini em uma única passagem
sudo sed -i -e 's/^memory_limit = .*/memory_limit = 128M/' \
-e 's/^max_execution_time = .*/max_execution_time = 30/' \
-e 's/^upload_max_filesize = .*/upload_max_filesize = 10M/' \
-e 's/^post_max_size = .*/post_max_size = 10M/' \
-e 's/^;*opcache.enable=.*/opcache.enable=1/' \
-e 's/^;*opcache.memory_consumption=.*/opcache.memory_consumption=64/' \
-e 's/^;*opcache.max_accelerated_files=.*/opcache.max_accelerated_files=4000/' \
-e 's/^;*opcache.revalidate_freq=.*/opcache.revalidate_freq=60/' \
/etc/php/8.4/fpm/php.ini
```

### 2.7 Reiniciar PHP-FPM
```bash
sudo systemctl restart php8.4-fpm
sudo systemctl enable php8.4-fpm
sudo systemctl status php8.4-fpm
```

---

## 3. Instalando o Nginx 1.24

### 3.1 Instalar Nginx
```bash
sudo apt install -y nginx

# Verificar versão
nginx -v

# Habilitar e iniciar
sudo systemctl enable nginx
sudo systemctl start nginx
sudo systemctl status nginx
```

### 3.2 Instalar Certbot (SSL)
O Certbot é a ferramenta recomendada para gerenciar certificados SSL gratuitos da Let's Encrypt.

```bash
# Atualizar snapd
sudo snap install core; sudo snap refresh core

# Instalar Certbot via Snap
sudo snap install --classic certbot

# Criar link simbólico para o comando certbot
sudo ln -s /snap/bin/certbot /usr/bin/certbot
```

---

## 4. Instalando MariaDB 10.11

### 4.1 Instalar MariaDB
```bash
sudo apt install -y mariadb-server mariadb-client
```

### 4.2 Verificar versão
```bash
mariadb --version
```

### 4.3 Executar script de segurança
```bash
sudo mysql_secure_installation
```

Se você acabou de instalar o MariaDB e ainda não definiu uma senha para o usuário root, simplesmente pressione **Enter** sem digitar nada quando solicitado.

O script fará uma série de perguntas. Para servidores Ubuntu modernos, siga estas recomendações:

1. **Switch to unix_socket authentication? [Y/n]**
   Responda: `Y`
   *Isso permite que o usuário root do sistema acesse o MariaDB via `sudo` sem precisar de uma senha separada.*

2. **Change the root password? [Y/n]**
   Responda: `n` (se estiver usando unix_socket acima).

3. **Remove anonymous users? [Y/n]**
   Responda: `Y`

4. **Disallow root login remotely? [Y/n]**
   Responda: `Y`

5. **Remove test database and access to it? [Y/n]**
   Responda: `Y`

6. **Reload privilege tables now? [Y/n]**
   Responda: `Y`

**Como acessar o banco após a configuração:**
Como configuramos o `unix_socket`, você acessará o console do MariaDB apenas com:
```bash
sudo mysql
```
Sem necessidade de senha.

### 4.4 Otimizar MariaDB para 1GB RAM
```bash
# Adicionar otimizações ao final do arquivo de configuração
sudo tee -a /etc/mysql/mariadb.conf.d/50-server.cnf > /dev/null << 'EOF'

# Otimizações para 1GB RAM
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
max_connections = 50
query_cache_size = 16M
query_cache_limit = 1M
tmp_table_size = 32M
max_heap_table_size = 32M
EOF

# Reiniciar MariaDB:
sudo systemctl restart mariadb
sudo systemctl enable mariadb
sudo systemctl status mariadb
```

---

## 5. Instalando e Configurando Redis

### 5.1 Instalar Redis Server
```bash
sudo apt install -y redis-server
```

### 5.2 Configurar Redis
```bash
# Fazer backup
sudo cp /etc/redis/redis.conf /etc/redis/redis.conf.bak

# Aplicar configurações de segurança e otimização:
sudo sed -i 's/^bind .*/bind 127.0.0.1 ::1/' /etc/redis/redis.conf
echo 'requirepass <SUA_SENHA_REDIS>' | sudo tee -a /etc/redis/redis.conf
sudo sed -i 's/^# maxmemory .*/maxmemory 128mb/' /etc/redis/redis.conf
sudo sed -i 's/^# maxmemory-policy .*/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
echo 'rename-command FLUSHDB ""' | sudo tee -a /etc/redis/redis.conf
echo 'rename-command FLUSHALL ""' | sudo tee -a /etc/redis/redis.conf
echo 'rename-command CONFIG ""' | sudo tee -a /etc/redis/redis.conf
```

### 5.3 Ajustar configurações do sistema para Redis
```bash
echo 'vm.overcommit_memory = 1' | sudo tee -a /etc/sysctl.conf
echo 'net.core.somaxconn = 511' | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

### 5.4 Desabilitar Transparent Huge Pages (THP)
```bash
echo 'echo never > /sys/kernel/mm/transparent_hugepage/enabled' | sudo tee -a /etc/rc.local
echo 'echo never > /sys/kernel/mm/transparent_hugepage/defrag' | sudo tee -a /etc/rc.local
sudo chmod +x /etc/rc.local
```

### 5.5 Reiniciar e habilitar Redis
```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server
sudo systemctl status redis-server
```

---

## 6. Instalando Composer e ACL

```bash
# Instalar ACL
sudo apt install acl -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

---

## 7. Estrutura de Diretórios

### 7.1 Criar diretório principal
Por padrão, aplicações web no Ubuntu ficam em `/var/www`. Vamos garantir que ele exista:

```bash
sudo mkdir -p /var/www
```

> A configuração específica de cada site (subpastas, logs e pools PHP) deve ser feita seguindo o **Guia de Deploy**.

---

## 8. Segurança e Firewall

### 8.1 Configurar Firewall (UFW)
```bash
# Instalar firewall
sudo apt install -y ufw

# Verificar status
sudo ufw status

# Definir Políticas Padrão
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Permitir SSH (essencial antes de ativar)
sudo ufw allow 22/tcp

# Permitir HTTP e HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Ativar firewall
sudo ufw enable
```

### 8.2 Ajuste para Oracle Cloud (Obrigatório se usar Oracle)
As instâncias da Oracle Cloud possuem regras de `iptables` que podem bloquear o tráfego mesmo com o UFW ativo. Execute os comandos abaixo para garantir a abertura das portas:

```bash
# Liberar portas 80 e 443 no iptables
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT

# Salvar regras para persistirem após reboot
sudo netfilter-persistent save
```

### 8.3 Instalar e Configurar Fail2ban
Enquanto o UFW bloqueia portas, o **Fail2Ban** monitora os logs do sistema e bane temporariamente IPs que apresentam comportamento suspeito (como errar a senha várias vezes), prevenindo ataques de força bruta.

1.  **Instalar o Fail2Ban:**
    ```bash
    sudo apt install fail2ban -y
    ```

2.  **Criar uma configuração local:**
    Sempre use arquivos `.local` para não perder suas mudanças em atualizações do sistema.
    ```bash
    sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
    ```

### 3. Configurar as regras básicas

```bash
sudo nano /etc/fail2ban/jail.local
```

Adicione ou ajuste o seguinte conteúdo:

```ini
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5
backend  = systemd

[sshd]
enabled = true
port    = ssh
backend = systemd
```

**Observação:** Não configure `logpath` manualmente. No Ubuntu 22.04, o Fail2Ban pode utilizar o systemd-journald diretamente para monitorar tentativas de login SSH.

### 4. Validar a configuração

```bash
sudo fail2ban-client -d
```

Se não houver erros, prossiga.

### 5. Reiniciar e verificar

```bash
sudo systemctl restart fail2ban
sudo systemctl enable fail2ban

# Verificar se o serviço iniciou corretamente
sudo systemctl status fail2ban

# Verificar o status da proteção SSH
sudo fail2ban-client status sshd
```

A saída do último comando deve mostrar algo semelhante a:

```text
Status for the jail: sshd
|- Filter
|  |- Currently failed: 0
|  |- Total failed: 0
|  `- Journal matches: ...
`- Actions
   |- Currently banned: 0
   |- Total banned: 0
   `- Banned IP list:
```


---

## 9. Endurecimento do SSH

Agora que você já testou o acesso via chave SSH com seu usuário administrativo, vamos fechar as portas para ataques de força bruta desativando o login por senha e o acesso direto ao `root`.

> **⚠️ AVISO CRÍTICO:** Certifique-se de que sua chave SSH está funcionando e que você consegue usar `sudo` com seu novo usuário **ANTES** de realizar este passo. Caso contrário, você poderá perder o acesso à VPS.

1.  **Editar as configurações do SSH:**
    ```bash
    sudo nano /etc/ssh/sshd_config
    ```

2.  **Ajuste (ou adicione) estas linhas:**
    ```text
    PermitRootLogin no
    PasswordAuthentication no
    PubkeyAuthentication yes
    ```
    *(Dica: Se as linhas estiverem comentadas com `#`, remova o `#`).*

3.  **Salvar e Sair:**
    -   `CTRL + O`, `Enter` para salvar.
    -   `CTRL + X` para sair.

4.  **Validar a configuração e reiniciar o serviço:**
    ```bash
    # Testa a sintaxe do arquivo de configuração antes de aplicar
    sudo sshd -t

    # Se não houver erros, reinicie o serviço
    sudo systemctl restart ssh
    ```

---

## 10. Monitoramento e Comandos Úteis

### 10.1 Verificar status dos serviços
```bash
systemctl status nginx
systemctl status php8.4-fpm
systemctl status mariadb
systemctl status redis-server
```

### 10.2 Monitorar logs
(Logs do sistema / serviços globais)

### 10.3 Verificar uso de recursos
`free -h`, `top`, `df -h`.

---

## 11. Manutenção e Limpeza

### 11.1 Criar script de limpeza de logs
```bash
sudo vim /usr/local/bin/clean-logs.sh
```
(Configurar cron job)
