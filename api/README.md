# API REST com Slim Framework e Arquitetura Limpa

API REST moderna construída com Slim Framework 4, PHP 8.4, fundamentada em princípios de Arquitetura Limpa (Clean Architecture) e Domain-Driven Design (DDD), e utilizando o padrão ADR (Action-Domain-Responder) na camada de apresentação.

Esta API serve como o backend para o portfólio, incluindo autenticação com JWT, manipulação de arquivos e uma estrutura organizada para escalabilidade.

## 📌 Índice

- [✨ Features](#-features)
- [🚀 Tecnologias](#-tecnologias)
- [🔧 Instalação e Execução (Docker)](#-instalação-e-execução-docker)
- [✅ Testes Automatizados](#-testes-automatizados)
- [🌱 Seeders](#-seeders)
- [🏗️ Arquitetura](#️-arquitetura)
- [📡 Documentação da API](#-documentação-da-api)

## ✨ Features

- **Autenticação com JWT**: Fluxo de Login (OTP ou Senha), Forgot/Reset de senha.
- **Controle de Acesso (RBAC)**: Proteção de rotas baseada em funções (`public`, `user`, `admin`).
- **Gerenciamento de Conteúdo**: CRUD completo para Projetos, Experiências e Tecnologias.
- **Upload de Arquivos**: Suporte para upload de imagens de projetos e tecnologias.
- **Segurança**:
  - Uso de DTOs para validação.
  - Senhas com hash.
  - Rate Limiting e CORS configuráveis.
- **Arquitetura Robusta**: Separação clara de responsabilidades (Application, Domain, Infrastructure) e uso do padrão ADR (Action-Domain-Responder) na camada de Presentation.
- **Ambiente Docker**: Ambiente containerizado com Nginx, PHP e MySQL.

### 🔐 Autenticação

A autenticação é baseada em **JWT (JSON Web Tokens)**.

- **Fluxo**: Após a validação de credenciais (Senha ou OTP), o serviço `JwtService` emite um token assinado (algoritmo `HS256`) contendo os dados do usuário e uma expiração de **1 hora**.
- **Requisições**: O token deve ser enviado no cabeçalho: `Authorization: Bearer <seu-token-jwt>`.
- **Logout**: A autenticação é *stateless*. O logout é tratado no lado do cliente (o cliente descarta o token). O token emitido permanece válido para acesso até o fim do tempo de expiração (1 hora) ou até que a expiração ocorra naturalmente.

### ⚡ Caching & Performance (Redis)

A API utiliza o **Redis** para otimizar a performance e garantir a segurança através de diferentes estratégias de cache:

- **Rate Limiting**: Controla o abuso da API limitando o número de requisições por IP ou por Usuário (via JWT).
  - **Chave**: `rate_limit:ip:<ip>` ou `rate_limit:user:<id>`.
- **OTP (One Time Password)**: Armazenamento temporário de códigos de acesso para login e recuperação de senha (expiração de 10 minutos).
  - **Chave**: `otp:<email>`.
- **Cache de Respostas HTTP (GET)**: Cache completo de respostas JSON para rotas de leitura.
  - **Funcionamento**: Apenas requisições `GET` bem-sucedidas (200 OK) são cacheadas por 1 hora.
  - **Chave**: `http:<recurso>:<caminho>:<hash_da_query>`.
  - **Invalidação Automática**: O cache de um recurso é invalidado automaticamente sempre que ocorre uma alteração bem-sucedida (`POST`, `PUT`, `PATCH`, `DELETE`) em seu respectivo endpoint administrativo (ex: alterar um projeto limpa o cache de `http:projects:*`).

#### 🧹 Limpeza Manual de Cache

Caso precise limpar o cache manualmente, você pode usar os seguintes comandos via Docker:

**1. Limpar tudo (Flush total do Redis):**
```bash
docker compose exec redis redis-cli -a ${REDIS_PASSWORD} FLUSHALL
```

**2. Limpar apenas o cache de respostas HTTP (Preserva OTPs e Rate Limits):**
```bash
docker compose exec redis redis-cli -a ${REDIS_PASSWORD} --scan --pattern "http:*" | xargs -r docker compose exec redis redis-cli -a ${REDIS_PASSWORD} DEL
```

---

## 🚀 Tecnologias

- **Slim Framework 4**
- **PHP-DI** (Injeção de Dependência)
- **MySQL 8.0**
- **Redis**: Utilizado para *Rate Limiting* e armazenamento temporário de códigos OTP.
- **JWT (Firebase)**
- **Monolog**
- **Docker & Docker Compose**

---

## 🔧 Instalação e Execução (Docker)

#### 1. Pré-requisitos
- Docker
- Docker Compose

#### 2. Configure o ambiente
```bash
cp .env.example .env
# Preencha as variáveis de ambiente necessárias no .env
```

#### 3. Inicie os containers
```bash
docker compose up -d --build
```

#### 4. Instale as dependências
```bash
docker compose exec api composer install
```

---

## ✅ Testes Automatizados

A API utiliza o **Pest PHP** para testes automatizados, integrando testes unitários (Service Layer) e testes de integração/feature (Actions e fluxos de API).

### 🧪 Tipos de Testes

- **Unitários:** Localizados em `tests/Unit`, focam na lógica de negócio isolada usando Mocks (Mockery).
- **Feature (Integração):** Localizados em `tests/Feature`, testam o ciclo completo da requisição até o banco de dados.

### ⚙️ Configuração do Ambiente de Testes

1. **Banco de Dados:** O ambiente Docker já provê um container `database_test` (MySQL na porta `3307`).
2. **Variáveis de Ambiente:** Certifique-se de que as variáveis `DB_TEST_*` no seu `.env` estão apontando para o container de teste.
3. **Preparação do Banco:**
```bash
# Entre no container do banco de teste e execute o schema/seed
docker exec -i api-database_test-1 mysql -u<user> -p<pass> <database> < database/schema.sql
docker exec -i api-database_test-1 mysql -u<user> -p<pass> <database> < database/seed.sql
```

### 🏃 Executando os Testes

Para rodar todos os testes:
```bash
docker compose exec api ./vendor/bin/pest
```

Para rodar um teste específico:
```bash
docker compose exec api ./vendor/bin/pest tests/Feature/ExperienceTest.php
```

Para rodar com cobertura de código (requer Xdebug):
```bash
docker compose exec api ./vendor/bin/pest --coverage
```

---

## 🌱 Seeders

Para popular o banco com dados iniciais (como roles de usuário):

```bash
docker compose exec api composer seed
```

---

## 🏗️ Arquitetura

O projeto segue uma arquitetura em camadas:

- **Domain Layer**: Entidades (`User`, `Project`, etc.), exceções e interfaces de repositórios.
- **Application Layer**: Casos de uso e DTOs.
- **Infrastructure Layer**: Implementações concretas (repositórios, mailer, etc.).
- **Presentation Layer (ADR)**: Actions, Responders e Middlewares (Slim). A camada adota o Action-Domain-Responder, onde cada endpoint possui uma Action única que delega o processamento e passa o resultado para um Responder responsável por formatar a saída HTTP (JSON).

### Estrutura
```
api/
├── config/                # Configurações e container DI
├── database/              # Schema e Seeds
├── src/
│   ├── Application/       # DTOs e serviços de aplicação
│   ├── Domain/            # Entidades e contratos
│   ├── Infrastructure/    # Persistência e serviços externos
│   └── Presentation/      # Actions e Responders
```

---

## 📡 Documentação da API

**Ver documentação completa:** [docs/API_DOCUMENTATION.md](./docs/API_DOCUMENTATION.md)

**Importar no Postman:** `docs/postman_collection.json`
