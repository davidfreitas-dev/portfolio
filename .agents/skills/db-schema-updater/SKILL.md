---
name: db-schema-updater
description: >-
  Use this skill when the user asks to modify, add, remove, or evolve the database schema 
  (tables, columns, indexes, constraints) in the portfolio API.
---

# Evolução de Schema de Banco de Dados (Sem Migrações/ORM)

Como o projeto não utiliza um ORM completo ou ferramentas de migração de banco de dados (ex: Phinx), qualquer evolução de schema precisa ser sincronizada manualmente em múltiplos pontos do código PHP e arquivos SQL.

## 🚀 Fluxo de Evolução de Schema

### 1. Modificar os Arquivos de Banco de Dados
- **Schema**: Atualize o arquivo **[`schema.sql`](file:///Users/davidfreitas/www/portfolio/api/database/schema.sql)** com a alteração desejada (ex: `ALTER TABLE ... ADD COLUMN ...` ou adicionando tabelas inteiras).
- **Seed**: Atualize o arquivo **[`seed.sql`](file:///Users/davidfreitas/www/portfolio/api/database/seed.sql)** se a alteração necessitar de novos valores padrão ou se alterar registros de teste.

### 2. Atualizar o Domínio (PHP)
- **Entidades**: Localize a entidade correspondente em `src/Domain/Entity/` e adicione a nova propriedade com tipagem estrita do PHP 8.4 (utilizando `readonly` e construtor apropriados).
- **Contratos**: Verifique se as novas colunas afetam as assinaturas de busca nos repositórios (ex: `findById`, `save`, etc.) em `src/Domain/Contract/`.

### 3. Atualizar a Persistência (SQL e Mapeamento)
- **Queries SQL**: Atualize o repositório em `src/Infrastructure/Persistence/` (ex: [`ProjectRepository.php`](file:///Users/davidfreitas/www/portfolio/api/src/Infrastructure/Persistence/ProjectRepository.php)) para incluir a nova coluna nos blocos de `INSERT`, `UPDATE` e `SELECT`.
- **Mapeamento (Hydration)**: Atualize o mapeador interno do repositório (ex: `mapRowToProject`) para ler o novo campo do banco e passá-lo ao construtor da Entidade.

### 4. Atualizar os DTOs
- Se o campo for preenchido pela API, adicione o campo e suas respectivas validações (`symfony/validator`) nos DTOs de request/response em `src/Application/DTO/`.

### 5. Atualizar e Executar nos Containers
Instrua o usuário (ou execute, caso tenha permissões) os comandos para aplicar a alteração nos bancos de desenvolvimento e de teste:

- **Banco de Desenvolvimento**:
  `docker exec -i habits-database-1 mysql -uroot -presu portfolio_db < api/database/schema.sql`
  `docker exec -i habits-database-1 mysql -uroot -presu portfolio_db < api/database/seed.sql`

- **Banco de Teste**:
  `docker exec -i habits-database_test-1 mysql -uroot -ptest_resu portfolio_test_db < api/database/schema.sql`
  `docker exec -i habits-database_test-1 mysql -uroot -ptest_resu portfolio_test_db < api/database/seed.sql`

---

## 🔍 Validação

Após alterar o banco de dados e os repositórios, rode os testes de integração de persistência para assegurar que nenhum mapeamento quebrou:
`docker compose exec api ./vendor/bin/pest tests/Feature/`
