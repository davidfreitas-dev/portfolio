# Documentação da API de Portfólio

## Índice
- [Geral](#geral)
- [Public API (Site)](#public-api-site)
  - [Experiências](#experiências-público)
  - [Projetos](#projetos-público)
  - [Tecnologias](#tecnologias-público)
  - [Imagens](#imagens)
- [CMS API (Admin)](#cms-api-admin)
  - [Autenticação](#autenticação)
  - [Perfil do Usuário](#perfil-do-usuário)
  - [Gestão de Experiências](#gestão-de-experiências)
  - [Gestão de Projetos](#gestão-de-projetos)
  - [Gestão de Tecnologias](#gestão-de-tecnologias)

---

## Geral

#### Welcome
```http
GET /
```
**Resposta (200 OK):**
```json
{
  "message": "Welcome to the Personal Portfolio Site API!"
}
```

#### Health Check
```http
GET /health
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Health check performed successfully",
  "data": {
    "status": "up",
    "version": "1.0.0",
    "database": "connected"
  }
}
```

---

## Public API (Site)

Endpoints utilizados pelo site estático para consumo de dados públicos. Todos retornam um objeto com `code`, `status`, `message` e a chave `data`.

### Experiências (Público)
Base path: `/public/experiences`

#### Listar Experiências
```http
GET /public/experiences?page=1&limit=10&search=
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Experiências listadas com sucesso.",
  "data": {
    "experiences": [
      {
        "id": 1,
        "title": "Desenvolvedor Full Stack",
        "description": "Atuação em projetos PHP e Vue.js",
        "start_date": "2023-01-01",
        "end_date": null,
        "sort_order": 0
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "pages": 1
  }
}
```

#### Obter detalhes da Experiência
```http
GET /public/experiences/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Experiência encontrada.",
  "data": {
    "id": 1,
    "title": "Desenvolvedor Full Stack",
    "description": "Atuação em projetos PHP e Vue.js",
    "start_date": "2023-01-01",
    "end_date": null,
    "sort_order": 0
  }
}
```

### Projetos (Público)
Base path: `/public/projects`

#### Listar Projetos
```http
GET /public/projects?page=1&limit=10&search=
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Projetos listados com sucesso.",
  "data": {
    "projects": [
      {
        "id": 1,
        "title": "Meu Portfólio",
        "description": "Site pessoal feito com Slim Framework e Vanilla JS",
        "slug": "meu-portfolio",
        "summary": "Resumo do projeto",
        "link": "https://meu-site.com",
        "github_link": "https://github.com/user/portfolio",
        "image": "https://api.meu-site.com/images/projects/portfolio.png",
        "technologies": [
          { "id": 1, "name": "PHP", "image": "php.png" }
        ]
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "pages": 1
  }
}
```

#### Obter detalhes do Projeto
```http
GET /public/projects/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Projeto encontrado.",
  "data": {
    "id": 1,
    "title": "Meu Portfólio",
    "description": "Descrição completa...",
    "slug": "meu-portfolio",
    "summary": "Resumo...",
    "link": "https://...",
    "github_link": "https://...",
    "image": "https://...",
    "technologies": [
      { "id": 1, "name": "PHP", "image": "php.png" }
    ]
  }
}
```

### Tecnologias (Público)
Base path: `/public/technologies`

#### Listar Tecnologias
```http
GET /public/technologies?page=1&limit=10&search=
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Tecnologias listadas com sucesso.",
  "data": {
    "technologies": [
      {
        "id": 1,
        "name": "PHP",
        "slug": "php",
        "image": "https://api.meu-site.com/images/technologies/php.png",
        "sort_order": 0
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "pages": 1
  }
}
```

#### Obter detalhes da Tecnologia
```http
GET /public/technologies/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Tecnologia encontrada.",
  "data": {
    "id": 1,
    "name": "PHP",
    "slug": "php",
    "image": "https://...",
    "sort_order": 0
  }
}
```

### Imagens
#### Obter Imagem
Retorna o arquivo binário da imagem.
```http
GET /images/{folder}/{image}
```

---

## CMS API (Admin)

Endpoints utilizados pelo sistema de gestão (CMS). Requerem autenticação JWT (exceto rotas de login). O token deve ser enviado no header `Authorization: Bearer {token}`.

### Autenticação
Base path: `/auth`

#### Solicitar Código de Login (OTP)
Envia um código para o e-mail do usuário.
```http
POST /auth/request-login
```
**Body (JSON):**
```json
{
  "email": "admin@portfolio.com"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Se o e-mail existir, um código OTP foi enviado."
}
```

#### Login
Autenticação por senha ou OTP.
```http
POST /auth/login
```
**Body (JSON - Senha):**
```json
{
  "email": "admin@portfolio.com",
  "password": "SenhaSegura"
}
```
**Body (JSON - OTP):**
```json
{
  "email": "admin@portfolio.com",
  "otp": "123456"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Login realizado com sucesso.",
  "data": {
    "token": "eyJhbGciOiJIUzI1...",
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "admin@portfolio.com",
      "role": "admin"
    }
  }
}
```

#### Logout
```http
POST /auth/logout
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Logout realizado com sucesso."
}
```

#### Reset de Senha (Esqueci a senha)

Utilizado para recuperação de conta quando o usuário esquece a senha.

##### 1. Solicitar Reset
Envia um código de verificação para o e-mail informado.
```http
POST /auth/forgot
```
**Body (JSON):**
```json
{
  "email": "admin@portfolio.com"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Se o e-mail existir, um código para redefinição foi enviado."
}
```

##### 2. Validar Código
Verifica se o código enviado por e-mail é válido para o usuário.
```http
POST /auth/validate-reset-code
```
**Body (JSON):**
```json
{
  "email": "admin@portfolio.com",
  "code": "123456"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Código validado com sucesso."
}
```

##### 3. Definir Nova Senha
Efetiva a alteração da senha utilizando o código validado.
```http
POST /auth/reset
```
**Body (JSON):**
```json
{
  "email": "admin@portfolio.com",
  "code": "123456",
  "password": "NovaSenhaSegura123",
  "password_confirm": "NovaSenhaSegura123"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Senha alterada com sucesso."
}
```

### Perfil do Usuário
Base path: `/users/me`

#### Obter perfil
```http
GET /users/me
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Perfil recuperado com sucesso.",
  "data": {
    "id": 1,
    "name": "João Silva",
    "email": "admin@portfolio.com",
    "phone": "11988887777",
    "role": "admin"
  }
}
```

#### Atualizar dados
```http
PUT /users/me
```
**Body (JSON):**
```json
{
  "name": "Nome Atualizado",
  "email": "novo@email.com",
  "phone": "11900001111"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Perfil atualizado com sucesso."
}
```

#### Alterar senha
```http
PATCH /users/me/change-password
```
**Body (JSON):**
```json
{
  "current_password": "SenhaAntiga",
  "new_password": "NovaSenha123",
  "new_password_confirm": "NovaSenha123"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Senha alterada com sucesso."
}
```

### Gestão de Experiências
Base path: `/admin/experiences`

#### Listar Experiências (Admin)
```http
GET /admin/experiences?page=1&limit=10&search=
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Experiências listadas com sucesso.",
  "data": {
    "experiences": [...],
    "total": 10,
    "page": 1,
    "limit": 10,
    "pages": 1
  }
}
```

#### Obter detalhes da Experiência (Admin)
```http
GET /admin/experiences/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Experiência encontrada.",
  "data": { "id": 1, "title": "...", ... }
}
```

#### Criar Experiência
```http
POST /admin/experiences
```
**Body (JSON):**
```json
{
  "title": "Sênior Dev",
  "description": "Descrição...",
  "start_date": "2020-01-01",
  "end_date": null,
  "sort_order": 5
}
```
**Resposta (201 Created):**
```json
{
  "code": 201,
  "status": "success",
  "message": "Experiência criada com sucesso.",
  "data": { "id": 10, "title": "Sênior Dev", ... }
}
```

#### Atualizar Experiência
```http
PUT /admin/experiences/{id}
```
**Body (JSON):** Todos os campos são opcionais.
```json
{
  "title": "Novo Título"
}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Experiência atualizada com sucesso."
}
```

#### Deletar Experiência
```http
DELETE /admin/experiences/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Experiência removida com sucesso."
}
```

### Gestão de Projetos
Base path: `/admin/projects`

#### Listar Projetos (Admin)
```http
GET /admin/projects?page=1&limit=10&search=
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Projetos listados com sucesso.",
  "data": {
    "projects": [...],
    "total": 5,
    "page": 1,
    "limit": 10,
    "pages": 1
  }
}
```

#### Obter detalhes do Projeto (Admin)
```http
GET /admin/projects/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Projeto encontrado.",
  "data": { "id": 1, "title": "...", ... }
}
```

#### Salvar Projeto (Criar/Atualizar)
Este endpoint utiliza `multipart/form-data` devido ao upload de imagem. Para atualizar, envie o campo `id`.
```http
POST /admin/projects
```
**Body (Multipart/form-data):**
- `id`: 1 (Opcional, apenas para atualização)
- `title`: "Novo Projeto"
- `description`: "Descrição longa..."
- `slug`: "novo-projeto" (Opcional)
- `summary`: "Resumo..." (Opcional)
- `link`: "https://..." (Opcional)
- `github_link`: "https://..." (Opcional)
- `sort_order`: 1 (Opcional)
- `is_active`: 1 (Opcional)
- `image`: [Arquivo Binário] (Opcional)
- `technology_ids[]`: [1, 2, 3] (Opcional)

**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Projeto salvo com sucesso.",
  "data": {
    "id": 1,
    "title": "Novo Projeto",
    "description": "Descrição longa...",
    "image": "https://...",
    "technologies": [...]
  }
}
```

#### Deletar Projeto
```http
DELETE /admin/projects/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Projeto removido com sucesso."
}
```

### Gestão de Tecnologias
Base path: `/admin/technologies`

#### Listar Tecnologias (Admin)
```http
GET /admin/technologies?page=1&limit=10&search=
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Tecnologias listadas com sucesso.",
  "data": {
    "technologies": [...],
    "total": 20,
    "page": 1,
    "limit": 10,
    "pages": 2
  }
}
```

#### Obter detalhes da Tecnologia (Admin)
```http
GET /admin/technologies/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Tecnologia encontrada.",
  "data": { "id": 1, "name": "...", ... }
}
```

#### Salvar Tecnologia (Criar/Atualizar)
Utiliza `multipart/form-data`.
```http
POST /admin/technologies
```
**Body (Multipart/form-data):**
- `id`: 1 (Opcional)
- `name`: "Node.js"
- `slug`: "node-js" (Opcional)
- `sort_order`: 1 (Opcional)
- `image`: [Arquivo Binário] (Opcional)

**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Tecnologia salva com sucesso.",
  "data": {
    "id": 1,
    "name": "Node.js",
    "image": "https://..."
  }
}
```

#### Deletar Tecnologia
```http
DELETE /admin/technologies/{id}
```
**Resposta (200 OK):**
```json
{
  "code": 200,
  "status": "success",
  "message": "Tecnologia removida com sucesso."
}
```
