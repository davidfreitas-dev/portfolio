---
name: create-api-feature
description: >-
  Use this skill when the user asks to implement a new feature, module, domain entity, 
  or a complete CRUD API endpoint in the portfolio REST API.
---

# Fluxo de Implementação de Novas Funcionalidades (Clean Architecture)

Este runbook deve ser seguido à risca para garantir a consistência das camadas e evitar erros de importação ou referências de código incompletas na API.

## 🚀 Fluxo de Desenvolvimento Sequencial

### Fase 1: Domínio (Core do Negócio)
1. **Entidades** (`src/Domain/Entity/`): Crie as classes de entidade usando propriedades promovidas (`public readonly`) do PHP 8.4 e tipagem estrita. Lembre-se de adicionar `declare(strict_types=1);` no início do arquivo.
2. **Exceções** (`src/Domain/Exception/`): Crie exceções de negócio semânticas (ex: `HabitNotFoundException`).
3. **Contratos/Interfaces** (`src/Domain/Contract/`): Defina as assinaturas das operações de repositório (ex: `ProjectRepositoryInterface.php`).

### Fase 2: Aplicação (Regras de Uso)
4. **DTOs de Request/Response** (`src/Application/DTO/`):
   - Crie os DTOs para validação de entrada usando atributos do `symfony/validator`.
   - Crie os DTOs de saída para formatar a resposta retornada pela API.
5. **Casos de Uso / Serviços** (`src/Application/Service/`): Implemente a lógica orquestradora (Service) que executa as regras do negócio e interage com as interfaces de repositório da Fase 1.

### Fase 3: Infraestrutura (Persistência e Configurações)
6. **Repositórios** (`src/Infrastructure/Persistence/`): Implemente o repositório concreto estendendo/implementando a interface definida na Fase 1, usando SQL puro com a classe `Database.php`.
7. **Schema SQL** (`database/schema.sql`): Adicione a definição das tabelas, chaves primárias, estrangeiras e índices necessários no arquivo de schema global.

### Fase 4: Apresentação (HTTP)
8. **Actions & Transformers** (`src/Presentation/Action/` e `src/Presentation/Transformer/`):
   - Crie a Action única que captura a requisição HTTP, valida se necessário e aciona o Service adequado.
   - Use o `JsonResponder` ou um `Transformer` adequado para formatar o DTO de saída no padrão do envelope JSON da API.
9. **Rotas e Injeção**:
   - Registre os endpoints da Action em `config/routes.php`.
   - Mapeie a interface do repositório para a classe de implementação concreta em `config/container.php`.

### Fase 5: Testes (Pest PHP)
10. **Testes Unitários**: Escreva os testes unitários da camada de serviço usando mocks em `tests/Unit/`.
11. **Testes de Integração/Feature**: Crie testes funcionais da API cobrindo os novos endpoints em `tests/Feature/`, herdando de `AppTestCase.php`.

---

## 📂 Modelos de Referência no Projeto

Sempre siga o estilo e padrão de código dos seguintes arquivos existentes no projeto para servir de guia em cada camada:

- **Domínio (Entidade)**: Veja [`src/Domain/Entity/Project.php`](file:///Users/davidfreitas/www/portfolio/api/src/Domain/Entity/Project.php) para uso de tipagem estrita e construtores.
- **Validação e DTO**: Veja [`src/Application/DTO/Project/ProjectRequestDTO.php`](file:///Users/davidfreitas/www/portfolio/api/src/Application/DTO/Project/ProjectRequestDTO.php) para ver como estruturar regras com atributos do `symfony/validator` e método `validate()`.
- **Persistência (Repositório SQL)**: Veja [`src/Infrastructure/Persistence/ProjectRepository.php`](file:///Users/davidfreitas/www/portfolio/api/src/Infrastructure/Persistence/ProjectRepository.php) para ver como mapear resultados do banco e executar queries parametrizadas com PDO.
- **Apresentação (Action ADR)**: Veja [`src/Presentation/Action/Project/Admin/SaveProjectAction.php`](file:///Users/davidfreitas/www/portfolio/api/src/Presentation/Action/Project/Admin/SaveProjectAction.php) para ver a injeção de dependências, validação do DTO e uso do `JsonResponder`.
- **Testes (Pest PHP)**: Veja [`tests/Feature/ProjectTest.php`](file:///Users/davidfreitas/www/portfolio/api/tests/Feature/ProjectTest.php) para ver a sintaxe correta de requisições e asserts de feature.

---

## 🛠️ Validação e Qualidade de Código

Ao finalizar todos os arquivos, execute os seguintes passos de validação no ambiente de desenvolvimento:

1. **Testes Automatizados**:
   `docker compose exec api ./vendor/bin/pest`
2. **Estilo de Código (PSR-12)**:
   `docker compose exec api composer cs-check`
   (ou use `docker compose exec api composer cs-fix` para ajustar automaticamente)
3. **Rector (Análise Estática)**:
   `docker compose exec api composer rector:dry`
