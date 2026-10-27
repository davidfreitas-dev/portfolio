# Domínio do Portfolio

O sistema é uma API REST desenvolvida para gerenciar e expor os dados de um Portfolio profissional e alimentar seu CMS administrativo (Frontend em Vue 3).

---

## 🏛️ Módulos e Entidades Principais

### 1. Projects (`App\Domain\Entity\Project`)
Representa os projetos exibidos no portfolio.
- **Campos Principais**: `id`, `title`, `slug`, `summary`, `description`, `image`, `link`, `githubLink`, `sortOrder`, `isActive`, `technologies`, `createdAt`, `updatedAt`, `deletedAt`.
- **Relações**: Relacionamento muitos-para-muitos com `Technology` (tabela intermediária `project_technologies`).
- **Comportamentos**: Soft delete (`deleted_at`), upload e exclusão de imagem de capa, controle de ordenação (`sort_order`).

### 2. Technologies (`App\Domain\Entity\Technology`)
Representa a stack de ferramentas, linguagens e frameworks.
- **Campos Principais**: `id`, `name`, `slug`, `category`, `image`, `sortOrder`, `isActive`, `createdAt`, `updatedAt`, `deletedAt`.
- **Comportamentos**: Categorização de tecnologias, vinculação a múltiplos projetos e experiências.

### 3. Experiences (`App\Domain\Entity\Experience`)
Histórico profissional e acadêmico.
- **Campos Principais**: `id`, `company`, `position`, `description`, `startDate`, `endDate`, `isCurrent`, `sortOrder`, `createdAt`, `updatedAt`, `deletedAt`.
- **Comportamentos**: Marcação de experiência atual (`isCurrent`), ordenação cronológica e por prioridade.

### 4. Users & Authentication (`App\Domain\Entity\User`)
Gestão de administradores e controle de acesso.
- **Campos Principais**: `id`, `name`, `email`, `passwordHash`, `role` (ex: `admin`), `isActive`, `createdAt`, `updatedAt`.
- **Fluxos de Segurança**:
  - Login com e-mail/senha gerando token JWT e cookie HttpOnly de Refresh.
  - Autenticação de dois fatores ou acesso por código temporário (OTP via Redis).
  - Fluxo de esqueci a senha (solicitação de código, validação e redefinição).
  - Alteração de senha autenticada (`ChangePasswordAction`).

### 5. Contact & Notifications (`App\Application\DTO\ContactRequestDTO`)
Canal de comunicação público do portfolio.
- **Campos**: `name`, `email`, `subject`, `message`.
- **Orquestração**: `ContactService` valida a mensagem e utiliza `MailService` (apoiado por `PHPMailerService`) para despachar notificação ao proprietário do portfolio.

### 6. Media & File Uploads (`App\Application\Service\FileUploaderService`)
Armazenamento e gerenciamento de arquivos de mídia (imagens de projetos, ícones de tecnologias).
- **Diretório base**: Configurado via variável de ambiente `STORAGE_PATH`.
- **Comportamentos**:
  - Geração de nomes únicos com prefixos semânticos.
  - Validação de formato/extensão e tamanho.
  - Limpeza automática de arquivos antigos ao substituir ou deletar um registro.
  - Endpoint de entrega de imagem via `GetImageAction` com headers corretos de cache e tipo MIME.

### 7. Logs & Observabilidade (`App\Domain\Entity\ErrorLog`)
Auditoria e persistência de erros não tratados ou avisos críticos do sistema via `ErrorLoggerService` e Monolog.

---

## 🔒 Diretrizes de Negócio

1. **Soft Delete**: Entidades públicas (Projects, Technologies, Experiences) utilizam exclusão lógica (`deleted_at IS NOT NULL`). Queries padrão de listagem devem sempre filtrar `WHERE deleted_at IS NULL`.
2. **Slug Automático**: Slugs devem ser únicos e gerados ou sanitizados a partir de títulos e nomes usando utilitários apropriados (`StringHelper::slugify`).
3. **Imagens Órfãs**: Ao atualizar ou remover um registro contendo arquivo em disco, sempre invoque `FileUploaderService::delete(...)` para não deixar arquivos órfãos no storage.
4. **Respostas Enriquecidas**: Os endpoints públicos e de admin entregam URLs absolutas de imagem (`image_url`) através dos Transformers, permitindo consumo direto pelo Frontend/CMS sem necessidade de concatenação no client.
