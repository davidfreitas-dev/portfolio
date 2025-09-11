# Personal Portfolio API

This template should help get you started developing with this API in Docker.

## Technologies Used

- Slim Framework 4: A micro-framework for PHP that helps you quickly write simple yet powerful web applications and APIs.
- JWT Auth: JSON Web Token authentication mechanism for securing API endpoints.
- Docker: Containerization platform used to ensure consistency and portability across environments.
- MySQL: Database management system utilized for storing application data.
- PHP DotEnv: Library for loading environment variables from `.env` files to configure the application.
- PHP Mailer: Library for sending emails from PHP applications.

## Build Containers

```sh
docker compose up -d
```

### Install Composer Dependencies

```sh
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer install
```

## Set Enviroment Variables

Create a .env file using .env.example and set variables. This variables are configs to connect to the database(MySQL), sending email(PHP Mailer) and JWT config tokens

See: 
[PHP DotEnv Configuration Reference](https://github.com/vlucas/phpdotenv)
[PHP Mailer Configuration Reference](https://github.com/PHPMailer/PHPMailer)

## Conecting to Database

The HOSTNAME in .env file should be the same of docker-compose file db:container_name

## Autenticação e Segurança

### Autenticação com JWT (JSON Web Token)

A API utiliza JWT (JSON Web Token) para autenticação. Abaixo estão os passos para autenticar e autorizar as requisições:

1. **Obtenção do Token JWT:**
   - Para acessar os recursos protegidos da API, você precisa obter um token JWT. Isso é feito enviando uma requisição `POST` para o endpoint `/signin` com as credenciais do usuário (e.g., e-mail e senha).

2. **Incluindo o Token nas Requisições:**
   - Após obter o token JWT, ele deve ser incluído no cabeçalho `Authorization` em todas as requisições subsequentes para acessar os recursos protegidos.

   **Formato do Cabeçalho:**

   ```
   Authorization: Bearer <token>
   ```

   **Exemplo de Requisição Autenticada:**

   ```http
   GET /users
   Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```

3. **Expiração do Token:**
   - O token JWT possui um tempo de expiração. Após esse período, será necessário obter um novo token através do processo de autenticação.
   - Se o token estiver expirado ou for inválido, a API retornará um erro `401 Unauthorized`.

4. **Rotas Protegidas:**
   - Todas as rotas que exigem autenticação são protegidas. A tentativa de acessar essas rotas sem um token válido resultará em um erro `401 Unauthorized`.

5. **Logout (Opcional):**
   - A API pode implementar um endpoint de logout que invalida o token JWT, garantindo que ele não possa mais ser usado. Este passo é opcional e depende da implementação específica da API.

## API Documentation

- [User Registration](#user-registration)
- [User Authentication](#user-authentication)
- [User Password Recovery Token](#user-password-recovery-token)
- [User Validate Token](#user-validate-token)
- [User Password Reset](#user-password-reset)
- [Experiences List](#experiences-list)
- [Experiences Details](#experiences-details)
- [Experiences Create](#experiences-create)
- [Experiences Update](#experiences-update)
- [Experiences Delete](#experiences-delete)
- [Technologies List](#technologies-list)
- [Technologies Details](#technologies-details)
- [Technologies Save](#technologies-save)
- [Technologies Delete](#technologies-delete)
- [Projects List](#projects-list)
- [Projects Page](#projects-page)
- [Project Details](#project-details)
- [Project Save](#project-save)
- [Project Delete](#project-delete)

#### User Registration

```http
  POST /signup
```

| Parameter     | Type     | Description                                             |
| :-----------  | :------- | :------------------------------------------------------ |
| `desperson`   | `string` | **Required**. User's full name                          |
| `deslogin`    | `string` | **Required**. User's username                           |
| `despassword` | `string` | **Required**. User's password                           |
| `desemail`    | `string` | **Required**. User's email address                      |
| `nrphone`     | `string` | User's phone number                                     |
| `nrcpf`       | `string` | User's CPF                                              |
| `inadmin`     | `integer`| **Required**. User's access level (1 = admin, 0 = user) |

**Observation:** The parameters above should be passed within a single JSON object.

**Response:** Data of the registered user

#### User Authentication

```http
  POST /signin
```

| Parameter     | Type     | Description                                             |
| :-----------  | :------- | :------------------------------------------------------ |
| `deslogin`    | `string` | User's username                                         |
| `desemail`    | `string` | User's email address                                    |
| `nrcpf`       | `string` | User's CPF                                              |
| `despassword` | `string` | **Required**. User's password                           |

**Note:** Authentication can be done using the username, email, or CPF along with the password.

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Data of the authenticated user

#### User Password Recovery Token

```http
  POST /forgot
```

| Parameter  | Type     | Description                                               |
| :--------- | :------- | :-------------------------------------------------------- |
| `desemail` | `string` | **Required**. User's email address                        |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void (The recovery token will be sent to e-mail)

#### User Validate Token

```http
  POST /forgot/token
```

| Parameter | Type     | Description                                                |
| :-------- | :------- | :--------------------------------------------------------- |
| `code`    | `string` | **Required**. Code sent with recovery token                |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void 

#### User Password Reset

```http
  POST /forgot/reset
```

| Parameter     | Type     | Description                                            |
| :------------ | :------- | :----------------------------------------------------- |
| `code`        | `string` | **Required**. Code sent with recovery token            |
| `despassword` | `string` | **Required**. New password                             |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void 

#### Experiences List

```http
  GET /experiences
```

| Parameter | Type      | Description                                          |
| :-------- | :-------- | :--------------------------------------------------- |
| `page`    | `integer` | **Required**. Page number                            |
| `limit`   | `integer` | **Required**. Number of items per page               |
| `search`  | `string`  | **Required**. Search term                            |

**Note:** Parameters should be passed as query strings

**Response:** List of experiences

#### Experiences Details

```http
  GET /experiences/id
```

**Note:** No parameters needed

**Response:** Experience data

#### Experiences Create

```http
  POST /experiences
```

| Parameter     | Type      | Description                                        |
| :------------ | :-------- | :------------------------------------------------- |
| `title`       | `string`  | **Required**. Experience title                     |
| `description` | `string`  | **Required**. Experience description               |
| `start_date`  | `string`  | **Required**. Experience start (Ex: Jan 2024)      |
| `end_date`    | `string`  | **Required**. Experience end (Ex: Dez 2024)        |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Experience data

#### Experiences Update

```http
  PUT /experiences/id
```

| Parameter     | Type      | Description                                        |
| :------------ | :-------- | :------------------------------------------------- |
| `id`          | `integer` | **Required**. Experience ID                        |
| `title`       | `string`  | **Required**. Experience title                     |
| `description` | `string`  | **Required**. Experience description               |
| `start_date`  | `string`  | **Required**. Experience start (Ex: Jan 2024)      |
| `end_date`    | `string`  | **Required**. Experience end (Ex: Dez 2024)        |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Experience data

#### Experiences Delete

```http
  DELETE /experiences/id
```

**Note:** No parameters needed

**Response:** Void

#### Technologies List

```http
  GET /technologies
```

| Parameter | Type      | Description                  |
| :-------- | :-------- | :--------------------------- |
| `page`    | `integer` | **Required**. Page number    |
| `limit`   | `integer` | **Required**. Items per page |
| `search`  | `string`  | **Required**. Search term    |

**Note:** Parameters should be passed as query strings

**Response:** List of technologies

#### Technologies Details

```http
  GET /technologies/id
```

**Note:** No parameters needed

**Response:** Technology data

#### Technologies Save

```http
  POST /technologies
```

| Parameter | Type      | Description                                 |
| :-------- | :-------- | :------------------------------------------ |
| `id`      | `integer` | Technology ID (for update, omit for create) |
| `name`    | `string`  | **Required**. Technology name               |
| `image`   | `string`  | Image file (binary), optional               |

**Observation:** Parameters should be passed as a FormData object with `Content-Type: multipart/form-data`.

**Response:** Technology data

#### Technologies Delete

```http
  DELETE /technologies/delete/id
```

**Note:** No parameters needed

**Response:** Void

#### Projects List

```http
  GET /projects
```

**Note:** No parameters needed.

**Response:** List of projects

#### Project Details

```http
  GET /projects/id
```

| Parameter   | Type      | Description                                             |
| :---------- | :-------- | :------------------------------------------------------ |
| `idproject` | `integer` | **Required**. Project ID                                |

**Response:** Project details

#### Projects Page

```http
  GET /projects/page/page
```

| Parameter      | Type      | Description                                          |
| :------------- | :-------- | :--------------------------------------------------- |
| `page`         | `integer` | **Required**. Page number                            |

**Response:** All projects 5 items per page

#### Project Save

```http
  POST /projects/save
```

| Parameter        | Type      | Description                                        |
| :--------------- | :-------- | :------------------------------------------------- |
| `idproject`      | `integer` | Project ID (to update)                             |
| `destitle`       | `string`  | **Required**. Project title                        |
| `desdescription` | `string`  | **Required**. Project description                  |
| `deslink`        | `string`  | Project link                                       |
| `image`          | `string`  | Project image file                                 |
| `technologies`   | `string`  | **Required**. String with techs ID's (Ex.: "1, 2") |

**Note:** When the Project ID is greater than zero, an update will be made based on the Project ID, otherwise an insertion will be made.

**Observation:** The parameters should be passed within a FormData object with Content-Type: multipart/form-data on headers.

**Response:** Project data

#### Project Delete

```http
  DELETE /projects/delete/id
```

| Parameter   | Type      | Description                                           |
| :---------- | :-------- | :---------------------------------------------------- |
| `idproject` | `integer` | **Required**. Project ID                              |

**Response:** Void