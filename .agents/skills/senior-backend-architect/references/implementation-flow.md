# Fluxo de Implementação Sequencial de Features (ADR)

Sempre que a tarefa for criar uma nova funcionalidade, entidade ou CRUD completo na API, siga rigorosamente a ordem sequencial abaixo (Fase 1 à Fase 5) para manter a integridade arquitetural e evitar referências incompletas ou erros de importação.

---

## 🚀 O Fluxo Sequencial

```
Fase 1: DOMÍNIO         (Entity -> Exception -> Contract)
       │
       ▼
Fase 2: APLICAÇÃO       (Request DTO -> Application Service)
       │
       ▼
Fase 3: INFRAESTRUTURA  (Repository PDO -> database/schema.sql)
       │
       ▼
Fase 4: APRESENTAÇÃO    (Transformer -> Action ADR -> routes.php -> container.php)
       │
       ▼
Fase 5: TESTES          (Pest Unit Tests -> Pest Feature Tests -> cs-fix -> rector)
```

---

### Fase 1: Domínio (Core do Negócio)

1. **Entidade (`src/Domain/Entity/<Entity>.php`)**:
   - Defina as propriedades usando `public readonly` do PHP 8.4 e tipagem estrita (`declare(strict_types=1);`).
   - Use `DateTimeImmutable` para timestamps.
   - Referência no projeto: [`src/Domain/Entity/Project.php`](file:///Users/davidfreitas/www/portfolio/api/src/Domain/Entity/Project.php).

2. **Exceções Semânticas (`src/Domain/Exception/<Entity>Exception.php`)**:
   - Crie exceções específicas para cenários de domínio (ex: `<Entity>NotFoundException`, `<Entity>ValidationException`).
   - Para exceções de validação, implemente uma forma de reter o array de erros dos campos violados.
   - Referência no projeto: [`src/Domain/Exception/ProjectNotFoundException.php`](file:///Users/davidfreitas/www/portfolio/api/src/Domain/Exception/ProjectNotFoundException.php) e [`src/Domain/Exception/ProjectValidationException.php`](file:///Users/davidfreitas/www/portfolio/api/src/Domain/Exception/ProjectValidationException.php).

3. **Contrato de Repositório (`src/Domain/Contract/<Entity>RepositoryInterface.php`)**:
   - Defina os métodos que o domínio necessita para persistência (ex: `findAll`, `findById`, `save`, `delete`).
   - Use tipos estritos de retorno (`?Entity`, `array`, `void`).
   - Referência no projeto: [`src/Domain/Contract/ProjectRepositoryInterface.php`](file:///Users/davidfreitas/www/portfolio/api/src/Domain/Contract/ProjectRepositoryInterface.php).

---

### Fase 2: Aplicação (Casos de Uso e Validação)

4. **DTO de Requisição (`src/Application/DTO/<Module>/<Entity>RequestDTO.php`)**:
   - Classe `readonly` com construtor promovido.
   - Decore os atributos com constraints do `symfony/validator` (`#[Assert\NotBlank]`, `#[Assert\Length]`, `#[Assert\Email]`, etc.).
   - Implemente o método `validate(ValidatorInterface $validator): void`, acumulando violações e disparando a `<Entity>ValidationException` se houver erros.
   - Referência no projeto: [`src/Application/DTO/Project/ProjectRequestDTO.php`](file:///Users/davidfreitas/www/portfolio/api/src/Application/DTO/Project/ProjectRequestDTO.php).

5. **Serviço de Aplicação (`src/Application/Service/<Entity>Service.php`)**:
   - Orquestra os fluxos da funcionalidade: valida o DTO, trata uploads de imagens (via `FileUploaderService`) e persiste no banco via o repositório da interface.
   - Aplica regras de negócio específicas (ex: regras de slug, cálculo de totais, soft deletes).
   - Injeta dependências via construtor (contrato de repositório, validador, serviços auxiliares).
   - Referência no projeto: [`src/Application/Service/ProjectService.php`](file:///Users/davidfreitas/www/portfolio/api/src/Application/Service/ProjectService.php).

---

### Fase 3: Infraestrutura (Persistência e Banco de Dados)

6. **Repositório Concreto (`src/Infrastructure/Persistence/<Entity>Repository.php`)**:
   - Implementa a interface `<Entity>RepositoryInterface`.
   - Injeta a classe `Database` (`App\Infrastructure\Persistence\Database`).
   - Executa queries preparadas com PDO (`$this->db->select(...)`, `$this->db->insert(...)`, etc.).
   - Converte os registros do banco (arrays associativos) para a Entidade através de um método helper privado `mapRowToEntity(array $row): Entity`.
   - Trata paginação (`LIMIT`, `OFFSET`) e filtros (ex: `deleted_at IS NULL`).
   - Referência no projeto: [`src/Infrastructure/Persistence/ProjectRepository.php`](file:///Users/davidfreitas/www/portfolio/api/src/Infrastructure/Persistence/ProjectRepository.php).

7. **Schema SQL (`database/schema.sql`)**:
   - Adicione ou atualize a DDL das tabelas no arquivo de schema global (`database/schema.sql`), incluindo chaves primárias, chaves estrangeiras, índices e campos de auditoria (`created_at`, `updated_at`, `deleted_at`).

---

### Fase 4: Apresentação (HTTP / ADR)

8. **Transformer (`src/Presentation/Transformer/<Entity>Transformer.php`)**:
   - Estende `AbstractTransformer` e implementa `TransformerInterface`.
   - Transforma a entidade em um array pronto para o retorno da API, formatando datas (`Y-m-d H:i:s`) e gerando URLs absolutas para imagens através do `$apiUrl` injetado.
   - Referência no projeto: [`src/Presentation/Transformer/ProjectTransformer.php`](file:///Users/davidfreitas/www/portfolio/api/src/Presentation/Transformer/ProjectTransformer.php).

9. **Action Única (`src/Presentation/Action/<Module>/[Admin/]<ActionName>.php`)**:
   - Implementa o método `__invoke(Request $request, Response $response, array $args = []): Response`.
   - Extrai dados da requisição (`$request->getParsedBody()`, `$request->getQueryParams()`, `$request->getUploadedFiles()`).
   - Monta o Request DTO e chama o método correspondente do `Service`.
   - Retorna a resposta padronizada via `JsonResponder` com o status HTTP adequado (`HTTPStatus::OK`, `HTTPStatus::CREATED`, etc.).
   - Referência no projeto: [`src/Presentation/Action/Project/Admin/SaveProjectAction.php`](file:///Users/davidfreitas/www/portfolio/api/src/Presentation/Action/Project/Admin/SaveProjectAction.php).

10. **Rotas e Injeção de Dependências**:
    - **Rotas (`config/routes.php`)**: Registre os novos endpoints no grupo público ou admin, anexando middlewares se necessário (`JwtAuthMiddleware`, `RoleMiddleware`, `HttpCacheMiddleware`, `HttpCacheInvalidationMiddleware`).
    - **Container (`config/container.php`)**: Registre a interface do repositório mapeada para a implementação concreta, além do Transformer caso receba argumentos customizados.

---

### Fase 5: Testes e Validação de Qualidade

11. **Testes Unitários (`tests/Unit/<Entity>ServiceTest.php`)**:
    - Teste os métodos do `Service` de forma isolada usando Pest PHP e Mockery para mockar o repositório e o validador.
    - Cubra caminhos felizes e disparos de exceções (`NotFoundException`, `ValidationException`).
    - Referência no projeto: [`tests/Unit/ProjectServiceTest.php`](file:///Users/davidfreitas/www/portfolio/api/tests/Unit/ProjectServiceTest.php).

12. **Testes de Feature / Integração (`tests/Feature/<Entity>Test.php`)**:
    - Estenda `Tests\AppTestCase`.
    - Simule requisições HTTP reais aos novos endpoints (`$this->createRequest()`, `$this->createJsonRequest()`, `$this->withAdminToken()`).
    - Valide o status code e o formato do envelope retornado (`$envelope['status'] === 'success'`).
    - Referência no projeto: [`tests/Feature/ProjectTest.php`](file:///Users/davidfreitas/www/portfolio/api/tests/Feature/ProjectTest.php).

13. **Verificação de Padrões e Qualidade**:
    - Execute a suíte de testes: `docker compose exec api ./vendor/bin/pest`
    - Verifique o estilo PSR-12: `docker compose exec api composer cs-check` (ou `docker compose exec api composer cs-fix`)
    - Análise estática com Rector: `docker compose exec api composer rector:dry`
