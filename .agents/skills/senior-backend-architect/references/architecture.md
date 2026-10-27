# Arquitetura do Projeto (ADR & Clean Architecture)

A API do Portfolio é construída em **PHP 8.4** com **Slim Framework 4**, utilizando o padrão **Action-Domain-Responder (ADR)** combinado com uma arquitetura em camadas inspirada em **Domain-Driven Design (DDD)** e **Clean Architecture**.

---

## 🧭 O Padrão ADR (Action-Domain-Responder)

Diferente do MVC tradicional onde um Controller concentra múltiplos métodos HTTP para um recurso, o ADR adota classes dedicadas de ação única (**Single-Action Classes**):

```
HTTP Request
     │
     ▼
┌────────────────────────────────────────────────────────┐
│ 1. ACTION (src/Presentation/Action/)                   │
│    - Invokable (__invoke)                              │
│    - Extrai dados (body, query params, uploaded files) │
│    - Instancia o Request DTO                           │
│    - Invoca o Application Service                      │
└────────────────────────────────────────────────────────┘
     │
     ▼
┌────────────────────────────────────────────────────────┐
│ 2. DOMAIN / APPLICATION (src/Application & Domain)     │
│    - DTO valida dados com symfony/validator            │
│    - Service orquestra regras de negócio e uploads     │
│    - Comunica-se com RepositoryInterface (Domain)      │
│    - Repositório concreto (Infrastructure) executa PDO │
│    - Retorna Entity ou Aggregate                       │
└────────────────────────────────────────────────────────┘
     │
     ▼
┌────────────────────────────────────────────────────────┐
│ 3. RESPONDER & TRANSFORMER (src/Presentation/)         │
│    - Transformer formata e enriquece a entidade        │
│    - JsonResponder encapsula no envelope padronizado   │
└────────────────────────────────────────────────────────┘
     │
     ▼
HTTP Response (JSON)
```

---

## 🏛️ Camadas da Aplicação

### 1. Presentation (`src/Presentation/`)
Responsável pela interface HTTP (entrada e saída).

- **Actions (`Action/`)**:
  - Uma classe por ação/endpoint (ex: `SaveProjectAction`, `GetProjectAction`, `DeleteProjectAction`).
  - Implementa o método mágico `__invoke(Request $request, Response $response, array $args = []): Response`.
  - Não contém lógica de negócio nem queries SQL.
  - Divide-se habitualmente em escopos `Admin/` (requer autenticação JWT) e endpoints públicos na raiz do módulo.
- **Transformers (`Transformer/`)**:
  - Estendem `AbstractTransformer` e implementam `TransformerInterface`.
  - Transformam entidades do domínio em arrays estruturados para consumo pela API (ex: gerando URLs absolutas para imagens via `apiUrl`).
- **Responder (`Responder/`)**:
  - `JsonResponder`: Utilitário central de resposta HTTP JSON com envelope padronizado.

### 2. Application (`src/Application/`)
Orquestração dos casos de uso e fronteira de validação.

- **DTOs (`DTO/`)**:
  - Classes `readonly` com tipagem estrita no PHP 8.4.
  - Atributos do `symfony/validator` (`#[Assert\NotBlank]`, `#[Assert\Length]`, etc.).
  - Método `validate(ValidatorInterface $validator): void` que dispara a exceção semântica de validação do domínio quando há violações.
- **Services (`Service/`)**:
  - Classes orquestradoras (ex: `ProjectService`, `TechnologyService`, `AuthService`).
  - Recebem dependências via injeção no construtor (`RepositoryInterface`, `ValidatorInterface`, `FileUploaderService`).
  - Executam a validação do DTO, tratam regras de negócio, gerenciam uploads de arquivos e chamam o repositório.

### 3. Domain (`src/Domain/`)
O núcleo de negócio, totalmente independente de frameworks e HTTP.

- **Entities (`Entity/`)**:
  - Classes ricas ou modelos de domínio com propriedades tipadas (`public readonly` ou com getters/setters protegidos).
  - Usam tipos nativos do PHP e `DateTimeImmutable`.
- **Contracts (`Contract/`)**:
  - Interfaces dos repositórios e serviços de infraestrutura (ex: `ProjectRepositoryInterface`, `MailerInterface`).
  - O domínio define a interface que a infraestrutura deve satisfazer (Inversão de Dependência).
- **Exceptions (`Exception/`)**:
  - Exceções semânticas de negócio (ex: `ProjectNotFoundException`, `ProjectValidationException`).
  - Capturadas pelo `ErrorMiddleware` para retornar os status HTTP correspondentes (404, 422, etc.).

### 4. Infrastructure (`src/Infrastructure/`)
Implementações técnicas de persistência, segurança, cache e comunicação externa.

- **Persistence (`Persistence/`)**:
  - Repositórios concretos (ex: `ProjectRepository`) implementando as interfaces de `src/Domain/Contract/`.
  - Usam a classe `Database` (PDO preparado) para executar queries SQL nativas seguras.
- **Caching & Redis (`Persistence/Redis/`)**:
  - Gerenciamento de cache HTTP, invalidação, OTP e rate limiting.
- **Middlewares (`Http/Middleware/`)**:
  - `JwtAuthMiddleware`: Validação do token Bearer JWT.
  - `RoleMiddleware`: Controle de permissões (ex: apenas admin).
  - `HttpCacheMiddleware`: Cache transparente de respostas GET no Redis.
  - `HttpCacheInvalidationMiddleware`: Invalidação de cache em mutações.
  - `RateLimitMiddleware`: Proteção contra abusos de requisições.
  - `ErrorMiddleware`: Formatação central de erros em JSON.
- **Security & Mailer**:
  - `AESCryptographer`: Criptografia reversível quando aplicável.
  - `PHPMailerService`: Envio de e-mails transacionais (contato, OTP).

---

## 📦 Padrão de Resposta JSON (Envelope)

Todas as respostas da API seguem envelopes padronizados emitidos pelo `JsonResponder`:

### Sucesso (200 / 201):
```json
{
  "code": 200,
  "status": "success",
  "message": "Projeto recuperado com sucesso.",
  "data": {
    "id": 1,
    "title": "Portfolio Web",
    "slug": "portfolio-web",
    "image_url": "https://api.exemplo.com/images/projects/portfolio.webp"
  }
}
```

### Sucesso com Paginação:
```json
{
  "code": 200,
  "status": "success",
  "message": "Lista de projetos retornada com sucesso.",
  "data": {
    "projects": [ ... ],
    "total_items": 42,
    "current_page": 1,
    "items_per_page": 10,
    "total_pages": 5
  }
}
```

### Erro de Validação (422 Unprocessable Entity):
```json
{
  "code": 422,
  "status": "error",
  "message": "Falha na validação dos dados.",
  "errors": {
    "title": "O título é obrigatório.",
    "description": "A descrição é obrigatória."
  }
}
```

### Erro de Recurso Não Encontrado (404 Not Found):
```json
{
  "code": 404,
  "status": "error",
  "message": "Projeto com ID 999 não encontrado."
}
```

---

## 🔌 Injeção de Dependência e Rotas

1. **Configuração de Rotas (`config/routes.php`)**:
   As rotas apontam diretamente para a classe da Action:
   ```php
   $app->group('/admin/projects', function (RouteCollectorProxy $group): void {
       $group->get('', AdminListProjectsAction::class);
       $group->get('/{id:[0-9]+}', AdminGetProjectAction::class);
       $group->post('', AdminSaveProjectAction::class);
       $group->delete('/{id:[0-9]+}', AdminDeleteProjectAction::class);
   })->add(RoleMiddleware::class)->add(JwtAuthMiddleware::class);
   ```

2. **Injeção de Dependências (`config/container.php`)**:
   Mapeie sempre as interfaces do Domain para suas implementações na Infrastructure:
   ```php
   ProjectRepositoryInterface::class => DI\autowire(ProjectRepository::class),
   ```
