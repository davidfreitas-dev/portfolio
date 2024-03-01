# Personal Portfolio Site API

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

## API Documentation

- [User Registration](#user-registration)
- [User Authentication](#user-authentication)
- [User Password Recovery Token](#user-password-recovery-token)
- [User Validate Token](#user-validate-token)
- [User Password Reset](#user-password-reset)
- [Experiences List](#experiences-list)
- [Experience Details](#experience-details)
- [Experience Create](#experience-Create)
- [Experience Update](#experience-update)
- [Experience Delete](#experience-delete)
- [Technologies List](#technologies-list)
- [Technology Details](#technology-details)
- [Technology Create](#technology-Create)
- [Technology Update](#technology-update)
- [Technology Delete](#technology-delete)
- [Projects List](#projects-list)
- [Project Details](#project-details)
- [Project Create/Update](#project-Create/Update)
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

**Note:** No parameters needed.

**Response:** List of experiences

#### Experience Details

```http
  GET /experiences/id
```

| Parameter      | Type      | Description                                          |
| :------------- | :-------- | :--------------------------------------------------- |
| `idexperience` | `integer` | **Required**. Experience ID                          |

**Response:** Experience details

#### Experience Create

```http
  POST /experiences/create
```

| Parameter        | Type     | Description                                         |
| :--------------- | :------- | :-------------------------------------------------- |
| `destitle`       | `string` | **Required**. Experience title                      |
| `desdescription` | `string` | **Required**. Experience description                |
| `dtstart`        | `string` | **Required**. Experience start (Ex: Jan 2024)       |
| `dtend`          | `string` | **Required**. Experience end (Ex: Dez 2024)         |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Experience Update

```http
  PUT /experiences/update/id
```

| Parameter        | Type      | Description                                        |
| :--------------- | :-------- | :------------------------------------------------- |
| `idexperience`   | `integer` | **Required**. Experience ID                        |
| `destitle`       | `string`  | **Required**. Experience title                     |
| `desdescription` | `string`  | **Required**. Experience description               |
| `dtstart`        | `string`  | **Required**. Experience start (Ex: Jan 2024)      |
| `dtend`          | `string`  | **Required**. Experience end (Ex: Dez 2024)        |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Experience Delete

```http
  DELETE /experiences/delete/id
```

| Parameter      | Type      | Description                                          |
| :------------- | :-------- | :--------------------------------------------------- |
| `idexperience` | `integer` | **Required**. Experience ID                          |

**Response:** Void

#### Technologies List

```http
  GET /technologies
```

**Note:** No parameters needed.

**Response:** List of technologies

#### Technology Details

```http
  GET /technologies/id
```

| Parameter      | Type      | Description                                         |
| :------------- | :-------- | :-------------------------------------------------- |
| `idtechnology` | `integer` | **Required**. Technology ID                         |

**Response:** Technology details

#### Technology Create

```http
  POST /technologies/create
```

| Parameter | Type     | Description                                               |
| :-------- | :------- | :-------------------------------------------------------- |
| `desname` | `string` | **Required**. Technology name                             |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Technology Update

```http
  PUT /technologies/update/id
```

| Parameter | Type     | Description                                               |
| :-------- | :------- | :-------------------------------------------------------- |
| `desname` | `string` | **Required**. Technology name                             |

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Technology Delete

```http
  DELETE /technologies/delete/id
```

| Parameter      | Type      | Description                                         |
| :------------- | :-------- | :-------------------------------------------------- |
| `idtechnology` | `integer` | **Required**. Technology ID                         |

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

#### Project Create/Update

```http
  POST /projects/save
```

| Parameter        | Type      | Description                                        |
| :--------------- | :-------- | :------------------------------------------------- |
| `idproject`      | `integer` | **Required**. Project ID                           |
| `destitle`       | `string`  | **Required**. Project title                        |
| `desdescription` | `string`  | **Required**. Project image name                   |
| `technologies`   | `string`  | **Required**. String with techs ID's (Ex.: "1, 2") |

**Note:** When the Project ID is greater than zero, an update will be made based on the Project ID, otherwise an insertion will be made.

**Observation:** The parameters should be passed within a single JSON object.

**Response:** Void

#### Project Delete

```http
  DELETE /projects/delete/id
```

| Parameter   | Type      | Description                                           |
| :---------- | :-------- | :---------------------------------------------------- |
| `idproject` | `integer` | **Required**. Project ID                              |

**Response:** Void