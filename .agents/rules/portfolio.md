# Project: Developer Guidelines

This document consolidates the guidelines for the project ecosystem:
- **REST API** — PHP 8.4 + Slim Framework 4.x (backend)
- **Frontend / CMS** — Vue 3 + Pinia (frontend)

---

## Table of Contents

1. [Critical Rules (All Projects)](#1-critical-rules-all-projects-)
2. [REST API](#2-rest-api)
   - [2.1 Overview](#21-overview)
   - [2.2 Architecture & Code Structure](#22-architecture--code-structure)
   - [2.3 Code Quality Standards](#23-code-quality-standards)
   - [2.4 Security & Caching (Redis)](#24-security--caching-redis)
   - [2.5 Development Workflow](#25-development-workflow)
   - [2.6 Commands Reference](#26-commands-reference)
   - [2.7 File Structure](#27-file-structure)
3. [Frontend / Portfolio CMS](#3-frontend--portfolio-cms)
   - [3.1 Overview](#31-overview)
   - [3.2 Architecture & Code Structure](#32-architecture--code-structure)
   - [3.3 Code Quality Standards](#33-code-quality-standards)
   - [3.4 Security & Authentication](#34-security--authentication)
   - [3.5 Design & UX](#35-design--ux)
   - [3.6 Development Workflow](#36-development-workflow)
4. [Quick Reference](#4-quick-reference)

---

## 1. Critical Rules (All Projects) ⚠️

These rules apply to **both projects** and must **NEVER** be violated under any circumstances:

### Rule #1: No Git Operations
**NEVER** use shell commands like `git add` or `git commit`. All Git operations must be done manually by the user. The assistant should only create, modify, or delete files as requested.

### Rule #2: No Hardcoded Credentials
- Never hardcode credentials, API keys, secrets, or Base URLs
- Always use environment variables and `.env` files for sensitive data
- Always ensure newly created sensitive files (`.env`, private keys, logs, temp files) are immediately added to `.gitignore`

---

## 2. REST API

### 2.1 Overview

| Property | Value |
|---|---|
| Environment | Docker-based (Nginx, PHP, MySQL, Redis) |
| PHP Version | 8.4 |
| Framework | Slim Framework 4.x |
| Language | Portuguese (messages & test output), English (code & docs) |

**Key Characteristics:**
- RESTful API architecture
- Layered architecture inspired by DDD and Clean Architecture
- Dependency injection using PHP-DI
- Docker-first development workflow
- Extensive use of Redis for Caching and Rate Limiting

> **Additional Critical Rule — No Local Composer**: **NEVER** run Composer locally. All Composer commands must be executed through Docker: `docker compose exec api composer <command>`

---

### 2.2 Architecture & Code Structure

#### Layered Architecture

| Layer | Location | Responsibility |
|---|---|---|
| Presentation | `src/Presentation` | Handle HTTP requests and responses (Actions, Responders, Middlewares) |
| Application | `src/Application` | Orchestrate business logic (Services, DTOs, Validation) |
| Domain | `src/Domain` | Core business logic (Entities, Value Objects, Repository Interfaces, Exceptions) |
| Infrastructure | `src/Infrastructure` | Technical implementations (Database Repositories, Caching, JWT, Mailers) |

**Dependency Direction**: Presentation → Application → Domain ← Infrastructure

#### Design Principles

- **SOLID Principles**: Applied throughout the codebase
- **Single Responsibility**: Keep methods small and focused on one task
- **Dependency Injection**: Use PHP-DI instead of instantiating objects directly
- **Composition Over Inheritance**: Prefer composition when appropriate
- **Interface Segregation**: Define focused repository interfaces in the Domain layer

---

### 2.3 Code Quality Standards

#### Code Style (PSR-12)

- Use strict type declarations at the beginning of all files:
  ```php
  declare(strict_types=1);
  ```
- Use type hints for all function parameters and return types
- Use class constants instead of magic strings
- Avoid global functions; prefer class methods or namespaced functions
- Always use **Constructor Property Promotion** to simplify class property declarations
- Follow PSR standards: PSR-4, PSR-7, PSR-12

#### Documentation (PHPDoc)

All new code must have proper PHPDoc blocks with:
- `@param` — Document all parameters
- `@return` — Document return types
- `@throws` — Document exceptions that can be thrown
- `@var` — Document class properties

---

### 2.4 Security & Caching (Redis)

#### Authentication (JWT)
- **Flow**: Authentication uses stateless JWT via `JwtService` (HS256) with a 1-hour expiration.
- **Header**: `Authorization: Bearer <token>`
- **Refresh**: Uses an `HttpOnly` refresh token cookie for rehydration.

#### Caching (Redis)
The API leverages Redis heavily for performance and security:
- **Rate Limiting**: Limits requests per IP or User ID (`rate_limit:ip:<ip>`, `rate_limit:user:<id>`).
- **OTP**: Temporary storage for login/recovery codes (`otp:<email>`).
- **HTTP Cache**: Successful GET requests are cached for 1 hour (`http:<resource>:<path>:<hash>`).
- **Cache Invalidation**: Modifying a resource (POST/PUT/DELETE) must automatically clear its associated HTTP cache.

---

### 2.5 Development Workflow

#### Validation
- Place data input validations using `symfony/validator` in Request DTOs
- Location: `src/Application/DTO`
- Validate at the application layer boundary

#### Testing Strategy (Pest PHP)

The project uses **Pest PHP** instead of standard PHPUnit.

| Type | Location | Purpose |
|---|---|---|
| Unit | `tests/Unit` | Test individual components and logic in isolation using Mockery |
| Feature | `tests/Feature` | Test API endpoints end-to-end, hitting the test database |

**Test Database Setup:**
The `database_test` container runs MySQL on port 3307. Ensure schema and seeds are loaded:
```bash
docker exec -i api-database_test-1 mysql -u<user> -p<pass> <database> < database/schema.sql
docker exec -i api-database_test-1 mysql -u<user> -p<pass> <database> < database/seed.sql
```

---

### 2.6 Commands Reference

#### Composer Commands
```bash
docker compose exec api composer install
docker compose exec api composer update
docker compose exec api composer require <package/name>
docker compose exec api composer seed          # Run database seeders
```

#### Testing Commands (Pest)
```bash
docker compose exec api ./vendor/bin/pest                           # Run all tests
docker compose exec api ./vendor/bin/pest tests/Feature/Test.php    # Run specific test
docker compose exec api ./vendor/bin/pest --coverage                # Run tests with coverage
```

#### Redis Cache Commands
```bash
docker compose exec redis redis-cli -a ${REDIS_PASSWORD} FLUSHALL   # Flush all cache
# Flush only HTTP cache:
docker compose exec redis redis-cli -a ${REDIS_PASSWORD} --scan --pattern "http:*" | xargs -r docker compose exec redis redis-cli -a ${REDIS_PASSWORD} DEL
```

---

### 2.7 File Structure

```
api/
├── config/                # Container, settings, and routes
├── database/              # Schema and Seeders
├── docs/                  # API Markdown and Postman Collection
├── src/
│   ├── Application/       # DTOs, Services, and Validation
│   ├── Domain/            # Entities, Contracts, and Exceptions
│   ├── Infrastructure/    # Persistence, Security, Mailer, Http/Middlewares
│   └── Presentation/      # Actions and Responders
├── tests/
│   ├── Unit/              # Pest unit tests
│   └── Feature/           # Pest feature tests
└── composer.json
```

---

## 3. Frontend / Portfolio CMS

### 3.1 Overview

| Property | Value |
|---|---|
| Environment | Docker-based (development server) |
| Framework | Vue 3 |
| Language | TypeScript (Composition API with `<script setup>`) |
| Build Tool | Vite |
| State Management | Pinia |
| Styling | TailwindCSS |

**Key Characteristics:**
- Single Page Application (SPA) with `vue-router`
- CMS Administrative panel for managing Portfolio resources (Projects, Experiences, Technologies)
- Modular architecture (Composables, Stores, Services)

> **Additional Critical Rule — Composition API Only**: **ALWAYS** use Vue 3 Composition API with `<script setup>` syntax. Avoid Options API.

---

### 3.2 Architecture & Code Structure

#### Layered Architecture

| Layer | Location | Responsibility |
|---|---|---|
| Views | `src/views` | Route-level components and page structure |
| Components | `src/components` | Reusable UI components |
| Services | `src/services` | API communication (e.g., `authService.ts`) |
| Stores | `src/stores` | Global application state (`authStore`, `projectStore`, etc.) |
| Composables | `src/composables` | Reusable logic and utilities |
| API Client | `src/api` | Axios configuration (`axios.ts`) |

#### Routing
- SPA handled by `vue-router`
- Lazy loading for route components
- Route guards / Middleware for authenticated routes

---

### 3.3 Code Quality Standards

#### Vue Style Guide
- Use `PascalCase` for component names in templates
- Use `kebab-case` for events (e.g., `@handle-change`)
- Keep components small and strictly focused on UI

#### Composables Pattern
- Use composables (`src/composables/`) to encapsulate reusable logic and lifecycle hooks

---

### 3.4 Security & Authentication

- Custom JWT Auth via the main API.
- **Access tokens** are kept only in memory within `authStore`.
- **Refresh Tokens** are strictly handled via `HttpOnly` cookies generated by the backend. The app hydrates the session via a silent request to `/auth/refresh` on bootstrap (`initAuth`).
- API interceptors handle `401 Unauthorized` responses and route guards protect `/dashboard` and internal pages.

---

### 3.5 Design & UX

- Styled with **TailwindCSS** for responsive utility-first design.
- Brand colors: Blue, Dark Gray, White.
- Native Dark Mode support.
- Focus on accessibility (`aria` attributes, high contrast, keyboard navigation).

---

### 3.6 Development Workflow

#### Testing Strategy
| Type | Tool | Focus |
|---|---|---|
| Unit / Component | Vitest + Vue Test Utils | Components, Composables, Stores |
| Target Coverage | All | Minimum 80% recommended |

---

## 4. Quick Reference

### Common Rules (Both Projects)
✅ Never use Git commands (`git add`, `git commit`)  
✅ Never hardcode credentials, API keys, or secrets  
✅ Always use environment variables for sensitive data  
✅ Always add sensitive files to `.gitignore`  

### REST API
✅ Always use Docker commands: `docker compose exec api composer <command>`  
✅ Never run Composer locally  
✅ Run tests with Pest: `docker compose exec api ./vendor/bin/pest`  
✅ Use strict types: `declare(strict_types=1);`  
✅ Use dependency injection (PHP-DI)  
✅ Understand Redis caching impacts when building GET vs POST/PUT endpoints

### Frontend
✅ Use `<script setup>` in all components  
✅ Follow the layered architecture (View → Store → Service → API)  
✅ Use `@/` alias for all imports  