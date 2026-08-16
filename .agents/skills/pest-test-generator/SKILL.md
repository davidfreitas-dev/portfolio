---
name: pest-test-generator
description: >-
  Use this skill when the user asks to write, generate, or refactor automated tests 
  using Pest PHP and Mockery in the portfolio API.
---

# Gerador de Testes Automatizados (Pest PHP & Mockery)

Esta skill orienta a escrita e execução de testes de alta qualidade alinhados com o ecossistema Pest PHP do projeto.

## 🧪 Tipos de Testes e Estrutura

### 1. Testes Unitários (`tests/Unit/`)
- **Foco**: Testar a lógica de negócio isolada (Services/Use Cases).
- **Mocks**: Use o Mockery para mockar as dependências e interfaces dos repositórios.
- **Estrutura**:
  ```php
  <?php

  declare(strict_types=1);

  use App\Application\Service\ProjectService;
  use App\Domain\Contract\ProjectRepositoryInterface;
  use App\Domain\Entity\Project;

  beforeEach(function () {
      $this->repository = Mockery::mock(ProjectRepositoryInterface::class);
      // Injetar dependências no Service
      $this->service = new ProjectService($this->repository, ...);
  });

  afterEach(function () {
      Mockery::close();
  });

  test('deve criar um projeto com sucesso', function () {
      $project = new Project(...);
      $this->repository->shouldReceive('save')->once()->andReturn($project);

      $result = $this->service->create(...);
      expect($result->title)->toBe('Test Title');
  });
  ```

### 2. Testes de Integração/Feature (`tests/Feature/`)
- **Foco**: Testar o fluxo completo da requisição HTTP (Middlewares -> Action -> Service -> DB -> Responder).
- **Classe Base**: Estenda sempre `Tests\AppTestCase`.
- **Estrutura**:
  ```php
  <?php

  declare(strict_types=1);

  namespace Tests\Feature;

  use Tests\AppTestCase;

  class ExampleTest extends AppTestCase
  {
      public function test_can_perform_endpoint_action(): void
      {
          // Criar request HTTP
          $request = $this->createJsonRequest('POST', '/admin/projects', [
              'title' => 'Test Project',
              'slug' => 'test-project',
              // ... dados
          ]);

          // Autenticar como Admin
          $request = $this->withAdminToken($request);

          // Executar request na aplicação Slim
          $response = $this->request($request);

          // Asserts
          $this->assertEquals(201, $response->getStatusCode());
          $envelope = json_decode((string)$response->getBody(), true);
          $this->assertEquals('success', $envelope['status']);
      }
  }
  ```

---

## 🏃 Execução de Testes via Docker

Sempre execute os testes após implementá-los para verificar sua corretude:

- Rodar todos os testes:
  `docker compose exec api ./vendor/bin/pest`
- Rodar apenas testes unitários:
  `docker compose exec api ./vendor/bin/pest tests/Unit/`
- Rodar um teste de feature específico:
  `docker compose exec api ./vendor/bin/pest tests/Feature/ProjectTest.php`
  (Veja como referência de teste de feature em [`tests/Feature/ProjectTest.php`](file:///Users/davidfreitas/www/portfolio/api/tests/Feature/ProjectTest.php))
