# Segurança, Caching e Redis

A API do Portfolio utiliza Redis intensivamente para desempenho, segurança e controle de sessões, além de seguir diretrizes rígidas de segurança em PHP 8.4.

---

## ⚡ Caching HTTP no Redis

Para otimizar o tempo de resposta dos endpoints públicos e da administração, a API implementa cache transparente de respostas HTTP via middlewares:

### 1. Leitura de Cache (`HttpCacheMiddleware`)
- **Alvo**: Requisições HTTP com método `GET`.
- **Chave de Cache**: `http:<resource>:<path>:<md5_hash_query_params>`
- **TTL Padrão**: 3600 segundos (1 hora).
- **Comportamento**: Se a chave existir no Redis, a resposta é entregue instantaneamente com o header `X-Cache: HIT`, evitando execução de controllers, services ou queries no MySQL.

### 2. Invalidação Automática (`HttpCacheInvalidationMiddleware`)
- **Alvo**: Requisições que causam mutação de dados (`POST`, `PUT`, `PATCH`, `DELETE`).
- **Comportamento**: Ao modificar qualquer recurso (ex: `/admin/projects`), o middleware identifica o recurso afetado e remove todas as chaves associadas no padrão `http:<resource>:*` do Redis.
- **Regra Crítica**: Sempre que criar um novo endpoint de alteração de dados, certifique-se de que a rota passe pelo `HttpCacheInvalidationMiddleware`.

### Comandos de Inspeção e Limpeza de Cache:
```bash
# Limpar todo o cache do Redis:
docker compose exec redis redis-cli -a ${REDIS_PASSWORD} FLUSHALL

# Limpar apenas as chaves de cache HTTP:
docker compose exec redis redis-cli -a ${REDIS_PASSWORD} --scan --pattern "http:*" | xargs -r docker compose exec redis redis-cli -a ${REDIS_PASSWORD} DEL
```

---

## 🛡️ Segurança e Autenticação

### 1. Autenticação JWT (Stateless)
- **Implementação**: `App\Application\Service\JwtService` usando algoritmo `HS256`.
- **Access Token**: Curta duração (1 hora), trafegado no header `Authorization: Bearer <token>`.
- **Refresh Token**: Trafegado exclusivamente via cookie seguro com flag `HttpOnly`, `Secure` e `SameSite=Strict`. Evita exposição a ataques XSS no navegador.
- **Middleware**: `JwtAuthMiddleware` intercepta rotas privadas, decodifica o payload e injeta os dados do usuário autenticado nos atributos da requisição (`$request->getAttribute('user')`).

### 2. Códigos Temporários (OTP)
- **Armazenamento**: Redis com TTL curto (ex: 10 a 15 minutos).
- **Chave**: `otp:<email>`
- **Usos**: Autenticação de dois fatores e redefinição de senha esquecida.
- **Validação**: Uma vez utilizado com sucesso ou após exceder o limite de tentativas, a chave do OTP é imediatamente apagada do Redis.

### 3. Rate Limiting (`RateLimitMiddleware`)
- Protege endpoints públicos e de autenticação contra abusos e ataques de força bruta.
- **Chaves no Redis**:
  - Por endereço IP: `rate_limit:ip:<ip_address>`
  - Por usuário autenticado: `rate_limit:user:<user_id>`
- Se o limite de requisições por janela temporal for excedido, o middleware retorna imediatamente `HTTP 429 Too Many Requests`.

---

## 📁 Segurança em Upload de Arquivos

O upload de arquivos de imagem é orquestrado por `App\Application\Service\FileUploaderService`:

1. **Validação de MIME Type e Extensão**: Somente imagens válidas (`image/jpeg`, `image/png`, `image/webp`) são aceitas.
2. **Nomes Sanitizados e Únicos**: Arquivos são renomeados para `prefixo-<uniqid>-<timestamp>.<ext>`, prevenindo sobrescrita acidental e ataque de path traversal.
3. **Isolamento de Diretórios**: Uploads são gravados fora do document root público quando possível ou servidos exclusivamente através de rotas controladas (`GetImageAction`), conferindo permissões e checando integridade.
4. **Limpeza de Arquivos Antigos**: Ao atualizar a imagem de um projeto ou tecnologia, o arquivo anterior deve ser imediatamente removido do disco.

---

## 🔒 Princípios Fundamentais de Segurança no Código

- **Prevenção de SQL Injection**: **NUNCA** concatene variáveis em strings SQL. Utilize sempre placeholders nomeados (`:param`) com `$this->db->select($sql, $params)` ou PDO statements preparados.
- **Sem Credenciais Hardcoded**: Jamais coloque senhas, chaves secretas ou credenciais no código. Leia sempre de variáveis de ambiente (`$_ENV[...]`).
- **Validação Rigorosa de Entrada**: Todo dado vindo do cliente HTTP deve ser validado via Request DTO com `symfony/validator` antes de alcançar a camada de persistência.
- **Vazamento de Exceções**: Nunca exponha traces de stack, queries SQL falhas ou mensagens internas do PDO ao cliente final. Em produção, retorne mensagens genéricas e registre o erro detalhado nos logs via `ErrorLoggerService`.
