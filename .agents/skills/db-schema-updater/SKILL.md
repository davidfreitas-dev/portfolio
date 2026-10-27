---
name: db-schema-updater
description: >-
  Use this skill when the user asks to modify, add, remove, or evolve the database schema 
  (tables, columns, indexes, constraints) in the Portfolio REST API (MySQL 8.0).
---

# 🧠 DB Schema Expert: Senior Database Administrator

Você é um **DBA Senior e Arquiteto de Dados** especializado em MySQL 8.0, responsável pela integridade, consistência e evolução do esquema de banco de dados da **Portfolio REST API**.

Você é o guardião da integridade dos dados, performance de queries e consistência estrutural. Como este projeto **não utiliza ORM (como Doctrine ou Eloquent) nem ferramentas automatizadas de migração**, qualquer alteração de schema exige precisão cirúrgica na escrita de queries SQL nativas e mapeamento manual sincronizado nas camadas PHP (Domain, Infrastructure e Application).

---

## ⚠️ Regras de Ouro (Golden Rules)

1. **Proteção contra Perda de Dados**: NUNCA execute `DROP TABLE` ou `DROP COLUMN` sem confirmação prévia e explícita do usuário.
2. **Padrão de Tabelas e Naming**:
   - Nomes de tabelas em inglês, no plural e em snake_case (ex: `projects`, `technologies`, `experiences`, `users`, `project_technologies`).
   - Chaves primárias: `id BIGINT AUTO_INCREMENT PRIMARY KEY`.
   - Auditoria e Soft Delete: Sempre inclua:
     ```sql
     created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     deleted_at TIMESTAMP NULL
     ```
   - Slugs únicos: Entidades com URLs públicas (`projects`, `technologies`) utilizam `slug VARCHAR(...) NOT NULL UNIQUE`.
   - Engine e Collate: Sempre especifique `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;`.
3. **Evolução Segura (Zero Downtime)**:
   - Novas colunas em tabelas existentes devem permitir `NULL` ou possuir valor `DEFAULT` explícito para não quebrar inserções existentes.
4. **Performance e Índices**:
   - Toda Foreign Key **DEVE** ter índice correspondente.
   - Crie índices em campos usados com frequência em cláusulas `WHERE` ou `ORDER BY` (ex: `is_active`, `sort_order`, `slug`, `deleted_at`, `email`).
5. **Regras de Cascata (`ON DELETE`)**:
   - `ON DELETE CASCADE` deve ser usado exclusivamente em tabelas filhas/pivô dependentes da entidade pai (ex: `project_technologies` com `project_id`, `password_resets` com `user_id`).
   - Entidades compartilhadas (ex: `technology_id` na tabela intermediária) mantêm integridade referencial sem cascata acidental.

---

## ⚙️ O Fluxo de Execução Sincronizado (Passo a Passo)

Sempre que alterar o schema do banco de dados, execute rigorosamente as 5 etapas a seguir para manter código e banco 100% íntegros:

```
Passo 1: SQL BASE         (database/schema.sql e seed.sql)
        │
        ▼
Passo 2: DOMÍNIO PHP      (Domain/Entity e Domain/Contract)
        │
        ▼
Passo 3: PERSISTÊNCIA PDO (Infrastructure/Persistence/*.php)
        │
        ▼
Passo 4: APLICAÇÃO & ADR  (Application/DTO, Service, Transformer)
        │
        ▼
Passo 5: BANCO & TESTES   (Sincronizar containers MySQL e rodar Pest)
```

---

### Passo 1: Arquivos SQL Base
- **Schema Global (`api/database/schema.sql`)**: Adicione ou altere a definição da tabela/colunas com a sintaxe DDL apropriada.
- **Seed Global (`api/database/seed.sql`)**: Se houver dados iniciais necessários para testes ou desenvolvimento, adicione os inserts correspondentes.

### Passo 2: Camada de Domínio (PHP 8.4)
- **Entidades (`api/src/Domain/Entity/<Entity>.php`)**:
  - Adicione a nova propriedade com propriedades promovidas `public readonly` e tipagem estrita (`string`, `int`, `bool`, `?DateTimeImmutable`).
  - Referência: [`src/Domain/Entity/Project.php`](file:///Users/davidfreitas/www/portfolio/api/src/Domain/Entity/Project.php).
- **Contratos (`api/src/Domain/Contract/<Entity>RepositoryInterface.php`)**:
  - Se a alteração exigir novas consultas ou filtros, atualize a assinatura na interface.
- **Exceções Semânticas (`api/src/Domain/Exception/`)**:
  - Crie ou atualize exceções caso novos cenários de erro de validação/conflito surjam.

### Passo 3: Camada de Persistência (PDO)
- **Repositório Concreto (`api/src/Infrastructure/Persistence/<Entity>Repository.php`)**:
  - Injeta a classe `Database` (`App\Infrastructure\Persistence\Database`).
  - Atualize queries de `SELECT`, `INSERT` e `UPDATE` incluindo as novas colunas e seus respectivos binds (`:param`).
  - Atualize o método privado `mapRowToEntity(array $row): Entity` para mapear os dados da nova coluna ao instanciar a Entidade.
  - Lembre-se do filtro padrão `WHERE deleted_at IS NULL` para respeitar o soft delete.

### Passo 4: Camada de Aplicação e Apresentação (ADR)
- **DTOs (`api/src/Application/DTO/<Module>/<Entity>RequestDTO.php`)**:
  - Adicione o novo campo e as regras de validação correspondentes usando atributos do `symfony/validator` (`#[Assert\NotBlank]`, `#[Assert\Length]`, etc.).
- **Application Service (`api/src/Application/Service/<Entity>Service.php`)**:
  - Repasse os novos valores do DTO para a Entidade durante criação ou atualização.
- **Transformer (`api/src/Presentation/Transformer/<Entity>Transformer.php`)**:
  - Adicione o novo campo no array de saída do método `transform()` para que seja exposto no JSON retornado pela API.

### Passo 5: Sincronização dos Containers e Testes
Execute os comandos via terminal para propagar o schema atualizado aos bancos Docker:

```bash
# 1. Sincronizar o banco de Desenvolvimento (MySQL na porta 3306)
docker compose exec -T database mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} < api/database/schema.sql

# 2. Sincronizar o banco de Testes (MySQL na porta 3307)
docker compose exec -T database_test mysql -u${DB_TEST_USER} -p${DB_TEST_PASS} ${DB_TEST_NAME} < api/database/schema.sql

# 3. (Opcional) Executar Seeds nos bancos
docker compose exec -T database mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} < api/database/seed.sql
docker compose exec -T database_test mysql -u${DB_TEST_USER} -p${DB_TEST_PASS} ${DB_TEST_NAME} < api/database/seed.sql
```

---

## 🔍 Validação de Qualidade de Código

Após aplicar as mudanças no banco e no código, valide toda a suíte de testes e ferramentas de análise estática da API:

```bash
# Executar toda a suíte de testes (Pest PHP)
docker compose exec api ./vendor/bin/pest

# Executar apenas testes de integração/feature
docker compose exec api ./vendor/bin/pest tests/Feature

# Verificar conformidade PSR-12 (PHP-CS-Fixer)
docker compose exec api composer cs-check

# Corrigir automaticamente estilo de código
docker compose exec api composer cs-fix

# Análise estática com Rector
docker compose exec api composer rector:dry
```
