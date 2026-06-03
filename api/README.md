# API REST com Slim Framework e Arquitetura Limpa.

API REST moderna construída com Slim Framework 4, PHP 8.4, e inspirada em princípios de Arquitetura Limpa (Clean Architecture) e Domain-Driven Design (DDD).

Esta API serve como o backend para o portfólio, incluindo autenticação com JWT, manipulação de arquivos e uma estrutura organizada para escalabilidade.

## 📌 Índice

- [✨ Features](#-features)
- [🚀 Tecnologias](#-tecnologias)
- [🔧 Instalação e Execução (Docker)](#-instalação-e-execução-docker)
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
- **Arquitetura Robusta**: Separação de camadas (Presentation, Application, Domain, Infrastructure).
- **Ambiente Docker**: Ambiente containerizado com Nginx, PHP e MySQL.

### 🔐 Autenticação

A autenticação é baseada em **JWT (JSON Web Tokens)**.

- **Fluxo**: Após a validação de credenciais (Senha ou OTP), o serviço `JwtService` emite um token assinado (algoritmo `HS256`) contendo os dados do usuário e uma expiração de **1 hora**.
- **Requisições**: O token deve ser enviado no cabeçalho: `Authorization: Bearer <seu-token-jwt>`.
- **Logout**: A autenticação é *stateless*. O logout é tratado no lado do cliente (o cliente descarta o token). O token emitido permanece válido para acesso até o fim do tempo de expiração (1 hora) ou até que a expiração ocorra naturalmente.

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
- **Presentation Layer**: Actions, Responders e Middlewares (Slim).

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
