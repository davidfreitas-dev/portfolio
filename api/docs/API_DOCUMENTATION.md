# Documentação da API de Portfólio

## Índice
- [Geral](#geral)
- [Autenticação](#autenticação)
- [Perfil do Usuário](#perfil-do-usuário)
- [Experiências](#experiências)
- [Projetos](#projetos)
- [Tecnologias](#tecnologias)

---

## Geral

#### Welcome
```http
GET /
```

#### Health Check
```http
GET /health
```

---

## Autenticação

Base path: `/auth`

#### Solicitar Código de Login (OTP)
Valida se o e-mail existe na base de dados e envia um código OTP para login.
```http
POST /auth/request-login
```

**Body:**
```json
{
  "email": "joao@example.com"
}
```

#### Login
Autentica o usuário utilizando e-mail com senha OU e-mail com código OTP.
```http
POST /auth/login
```

**Body (Senha):**
```json
{
  "email": "joao@example.com",
  "password": "senha123"
}
```

**Body (OTP):**
```json
{
  "email": "joao@example.com",
  "otp": "123456"
}
```

#### Logout
Encerra a sessão do usuário.
```http
POST /auth/logout
```
Requer token JWT no header `Authorization: Bearer {token}`.

#### Solicitar Reset de Senha
```http
POST /auth/forgot
```

**Body:**
```json
{
  "email": "joao@example.com"
}
```

#### Validar Código de Reset
```http
POST /auth/validate-reset-code
```

**Body:**
```json
{
  "email": "joao@example.com",
  "code": "123456"
}
```

#### Reset de Senha
```http
POST /auth/reset
```

**Body:**
```json
{
  "email": "joao@example.com",
  "code": "123456",
  "password": "novaSenha123",
  "password_confirm": "novaSenha123"
}
```

---

## Perfil do Usuário

#### Obter perfil do usuário logado
Requer token JWT.

```http
GET /users/me
```

#### Atualizar dados do perfil
Requer token JWT.

```http
PUT /users/me
```

**Body:**
```json
{
  "name": "Nome Atualizado",
  "email": "novo@example.com",
  "phone": "99988877766"
}
```

#### Alterar senha
Requer token JWT.

```http
PATCH /users/me/change-password
```

**Body:**
```json
{
  "current_password": "senhaAtual",
  "new_password": "novaSenha123",
  "new_password_confirm": "novaSenha123"
}
```

#### Deletar conta
Requer token JWT.

```http
DELETE /users/me
```

---

## Experiências

Base path: `/experiences`
Requer permissões apropriadas (pública para listagem, admin para gestão).

#### Listar Experiências
```http
GET /experiences?page=1&limit=10&search=
```

#### Obter detalhes da Experiência
```http
GET /experiences/{id}
```

#### Criar Experiência (Admin)
```http
POST /experiences
```

**Body:**
```json
{
  "title": "Cargo",
  "description": "Descrição das atividades",
  "start_date": "2023-01-01",
  "end_date": "2023-12-31",
  "sort_order": 1
}
```

#### Atualizar Experiência (Admin)
```http
PUT /experiences/{id}
```

**Body:**
```json
{
  "title": "Cargo Atualizado",
  "description": "Descrição atualizada",
  "start_date": "2023-01-01",
  "end_date": null,
  "sort_order": 1
}
```

#### Deletar Experiência (Admin)
```http
DELETE /experiences/{id}
```

---

## Projetos

Base path: `/projects`
Requer permissões apropriadas (pública para listagem, admin para gestão).

#### Listar Projetos
```http
GET /projects?page=1&limit=10&search=
```

#### Obter detalhes do Projeto
```http
GET /projects/{id}
```

#### Criar Projeto (Admin)
```http
POST /projects
```

**Body (Multipart/form-data):**
- `title`: string (obrigatório)
- `description`: string (obrigatório)
- `slug`: string (opcional)
- `summary`: string (opcional)
- `link`: string (opcional)
- `github_link`: string (opcional)
- `sort_order`: int (opcional)
- `is_active`: bool (opcional)
- `image`: file (opcional)

#### Atualizar Projeto (Admin)
```http
PUT /projects/{id}
```

**Body (Multipart/form-data ou x-www-form-urlencoded):**
- Mesmos campos do **Criar Projeto**.
- Nota: Para manter a imagem atual, não envie o campo `image`.

#### Deletar Projeto (Admin)
```http
DELETE /projects/{id}
```

---

## Tecnologias

Base path: `/technologies`
Requer permissões apropriadas (pública para listagem, admin para gestão).

#### Listar Tecnologias
```http
GET /technologies?page=1&limit=10&search=
```

#### Obter detalhes da Tecnologia
```http
GET /technologies/{id}
```

#### Criar Tecnologia (Admin)
```http
POST /technologies
```

**Body (Multipart/form-data):**
- `name`: string (obrigatório)
- `slug`: string (opcional)
- `sort_order`: int (opcional)
- `image`: file (opcional)

#### Atualizar Tecnologia (Admin)
```http
PUT /technologies/{id}
```

**Body (Multipart/form-data ou x-www-form-urlencoded):**
Mesmos campos do POST.

#### Deletar Tecnologia (Admin)
```http
DELETE /technologies/{id}
```

---

