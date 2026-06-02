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

**Query Parameters:**
- `page`: int (opcional, padrão: 1)
- `limit`: int (opcional, padrão: 10)
- `search`: string (opcional)

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
  "sort_order": 0
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
  "sort_order": 0
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

**Query Parameters:**
- `page`: int (opcional, padrão: 1)
- `limit`: int (opcional, padrão: 10)
- `search`: string (opcional)

#### Obter detalhes do Projeto
```http
GET /projects/{id}
```

#### Salvar Projeto (Criar/Atualizar) (Admin)
Utilize esta rota para criar ou atualizar um projeto. Se o campo `id` for enviado no corpo da requisição, o projeto será atualizado. Esta rota utiliza `multipart/form-data` para suportar upload de imagens.
```http
POST /projects
```

**Body (Multipart/form-data):**
- `id`: int (opcional - enviar apenas para atualizar)
- `title`: string (obrigatório)
- `description`: string (obrigatório)
- `slug`: string (opcional)
- `summary`: string (opcional)
- `link`: string (opcional)
- `github_link`: string (opcional)
- `sort_order`: int (opcional, padrão: 0)
- `is_active`: bool (opcional, padrão: true)
- `image`: file (opcional)
- `technology_ids[]`: array of int (opcional) - Ex: `technology_ids[0]=1&technology_ids[1]=2`

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

**Query Parameters:**
- `page`: int (opcional, padrão: 1)
- `limit`: int (opcional, padrão: 10)
- `search`: string (opcional)

#### Obter detalhes da Tecnologia
```http
GET /technologies/{id}
```

#### Salvar Tecnologia (Criar/Atualizar) (Admin)
Utilize esta rota para criar ou atualizar uma tecnologia. Se o campo `id` for enviado no corpo da requisição, a tecnologia será atualizada. Esta rota utiliza `multipart/form-data` para suportar upload de imagens.
```http
POST /technologies
```

**Body (Multipart/form-data):**
- `id`: int (opcional - enviar apenas para atualizar)
- `name`: string (obrigatório)
- `slug`: string (opcional)
- `sort_order`: int (opcional, padrão: 0)
- `image`: file (opcional)

#### Deletar Tecnologia (Admin)
```http
DELETE /technologies/{id}
```

---

