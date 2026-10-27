# Testes e Validação de Qualidade de Código (Pest PHP)

Toda nova implementação, ajuste ou refatoração no Backend deve ser acompanhada por testes automatizados e validações de qualidade executadas dentro do container Docker da aplicação.

---

## ⚡ Comandos de Execução

> **Regra Crítica**: **NUNCA** execute o Composer ou o Pest localmente na máquina host. Execute sempre através do Docker:

```bash
# 1. Executar toda a suíte de testes (Pest PHP)
docker compose exec api ./vendor/bin/pest

# 2. Executar apenas os testes unitários
docker compose exec api ./vendor/bin/pest tests/Unit

# 3. Executar apenas os testes de feature
docker compose exec api ./vendor/bin/pest tests/Feature

# 4. Executar um arquivo de teste específico
docker compose exec api ./vendor/bin/pest tests/Feature/ProjectTest.php
docker compose exec api ./vendor/bin/pest tests/Unit/ProjectServiceTest.php

# 5. Executar com relatório de cobertura
docker compose exec api ./vendor/bin/pest --coverage

# 6. Verificar conformidade com estilo PSR-12 (PHP-CS-Fixer)
docker compose exec api composer cs-check

# 7. Corrigir automaticamente estilo de código
docker compose exec api composer cs-fix

# 8. Análise estática e refatoração automática (Rector)
docker compose exec api composer rector:dry
```

---

## 🧪 Estrutura dos Testes

O projeto adota o **Pest PHP** com **Mockery** para mocks e isolamento.

### 1. Testes Unitários (`tests/Unit/`)
Focam na lógica de orquestração dos serviços (`Application\Service`) e regras de domínio em isolamento estrito, sem conexão real com banco de dados.

```php
<?php

declare(strict_types=1);

use App\Application\DTO\Project\ProjectRequestDTO;
use App\Application\Service\FileUploaderService;
use App\Application\Service\ProjectService;
use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Entity\Project;
use App\Domain\Exception\ProjectNotFoundException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

beforeEach(function (): void {
    $this->repository = Mockery::mock(ProjectRepositoryInterface::class);
    $this->validator = Mockery::mock(ValidatorInterface::class);
    $this->fileUploader = Mockery::mock(FileUploaderService::class);

    $this->service = new ProjectService(
        $this->repository,
        $this->validator,
        $this->fileUploader,
    );
});

afterEach(function (): void {
    Mockery::close();
});

test('deve obter projeto existente por ID com sucesso', function (): void {
    $project = new Project(
        id: 1,
        title: 'Projeto Alpha',
        description: 'Descrição de teste',
    );

    $this->repository
        ->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($project);

    $result = $this->service->getProject(1);

    expect($result)->toBeInstanceOf(Project::class)
        ->and($result->id)->toBe(1)
        ->and($result->title)->toBe('Projeto Alpha');
});

test('deve lançar ProjectNotFoundException ao buscar ID inexistente', function (): void {
    $this->repository
        ->shouldReceive('findById')
        ->once()
        ->with(999)
        ->andReturnNull();

    expect(fn () => $this->service->getProject(999))
        ->toThrow(ProjectNotFoundException::class);
});
```

### 2. Testes de Feature / Integração (`tests/Feature/`)
Validam o fluxo HTTP completo ponta a ponta: Middlewares -> Action -> Service -> Database -> Transformer -> JsonResponder.

Estendem sempre `Tests\AppTestCase` para usufruir dos helpers de requisição:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\AppTestCase;

class ProjectTest extends AppTestCase
{
    public function test_can_list_projects(): void
    {
        $request = $this->createRequest('GET', '/public/projects');
        $response = $this->request($request);

        $this->assertEquals(200, $response->getStatusCode());

        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertArrayHasKey('projects', $envelope['data']);
        $this->assertArrayHasKey('total_items', $envelope['data']);
    }

    public function test_can_create_project_as_admin(): void
    {
        $payload = [
            'title' => 'Projeto Novo ' . uniqid(),
            'description' => 'Descrição detalhada do projeto.',
            'slug' => 'projeto-novo-' . uniqid(),
            'summary' => 'Resumo do projeto',
            'sort_order' => 1,
            'is_active' => true,
        ];

        $request = $this->createJsonRequest('POST', '/admin/projects', $payload);
        $request = $this->withAdminToken($request);

        $response = $this->request($request);

        $this->assertEquals(201, $response->getStatusCode());

        $envelope = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $envelope['status']);
        $this->assertEquals($payload['title'], $envelope['data']['title']);
    }

    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $request = $this->createRequest('GET', '/admin/projects');
        $response = $this->request($request);

        $this->assertEquals(401, $response->getStatusCode());
    }
}
```

---

## 🎯 Cenários Obrigatórios de Teste

Ao projetar testes para qualquer novo endpoint ou serviço, cubra impreterivelmente:

1. **Caminho Feliz (Happy Path)**:
   - Respostas esperadas (HTTP 200 / 201) com estrutura de envelope íntegra.
2. **Validação de Dados Inválidos**:
   - DTO rejeita campos obrigatórios vazios ou formatações erradas (HTTP 422).
3. **Autenticação e Autorização**:
   - Acesso a rotas administrativas sem token retorna HTTP 401.
   - Acesso com role incompatível retorna HTTP 403.
4. **Recurso Não Encontrado**:
   - Busca por ID inexistente dispara NotFoundException e retorna HTTP 404.
5. **Comportamento de Soft Delete**:
   - Itens marcados como deletados não devem aparecer nas listagens públicas.
