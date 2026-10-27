# Arquitetura Frontend e Fluxo de Dados (Portfolio CMS)

A aplicação **Portfolio CMS** (`cms/`) é uma SPA desenvolvida com **Vue 3.5**, **Vite 6**, **Pinia 3**, **Vue Router 4** e **TailwindCSS 4**.

O fluxo de dados é estritamente unidirecional e estratificado em camadas de responsabilidade bem definidas.

---

## 🧭 O Fluxo Unidirecional de Dados

```
┌────────────────────────────────────────────────────────┐
│ 1. VIEW (src/views/)                                   │
│    - Componente Inteligente (Smart Component)          │
│    - Conecta com a Store (Pinia)                       │
│    - Controla estado de paginação, busca e filtros     │
│    - Abre/Fecha Modais (Modal.vue) e Diálogos (Dialog) │
└────────────────────────────────────────────────────────┘
     │                    ▲
     │ props              │ emits (@submit, @confirm)
     ▼                    │
┌─────────────────────────┴──────────────────────────────┐
│ 2. FORM / COMPONENT (src/forms/ & src/components/)     │
│    - Form: Valida com Vuelidate e emite @submit        │
│    - UI Components: Renderizam botões, inputs, tabelas │
└────────────────────────────────────────────────────────┘
     │
     ▼
┌────────────────────────────────────────────────────────┐
│ 3. STORE (src/stores/)                                 │
│    - Gerenciador de estado reativo global (Pinia)      │
│    - Guarda arrays reativos (projects, totalItems, etc)│
│    - Monta FormData para multipart/form-data           │
│    - Invoca os métodos do Service correspondente       │
└────────────────────────────────────────────────────────┘
     │
     ▼
┌────────────────────────────────────────────────────────┐
│ 4. SERVICE (src/services/)                             │
│    - Agrupa chamadas HTTP por recurso                  │
│    - Recebe e passa parâmetros tipados                 │
│    - Não armazena estado                               │
└────────────────────────────────────────────────────────┘
     │
     ▼
┌────────────────────────────────────────────────────────┐
│ 5. API CLIENT (src/api/index.ts)                       │
│    - Instância tipada do Axios (ApiInstance)           │
│    - Injeta Authorization Header com Bearer JWT        │
│    - Intercepta 401 para silent refresh (/auth/refresh)│
│    - Envia requisições com withCredentials: true       │
└────────────────────────────────────────────────────────┘
```

---

## 🏛️ Divisão de Responsabilidades

### 1. Views (`src/views/`)
- São os pontos de entrada roteados (`src/router/index.ts`).
- Exemplos: `Projects.vue`, `Technologies.vue`, `Experiences.vue`, `Home.vue`, `Profile.vue`.
- **Responsabilidades**:
  - Injetar a Store correspondente (`const projectsStore = useProjectsStore()`).
  - Utilizar composables globais de UX: `useLoading` para estado de carregamento e `useToast` para notificações.
  - Manter o estado de paginação (`page`, `itemsPerPage`), filtros e busca com debounce.
  - Orquestrar a abertura de modais (`Modal`, `Dialog`) através de template refs tipados (`ModalExpose`).
  - Passar dados planos como `props` para os componentes e formulários filhos.

### 2. Formulários (`src/forms/`)
- Formulários desacoplados das telas principais para manter as Views legíveis.
- Exemplos: `ProjectForm.vue`, `TechnologyForm.vue`, `ExperienceForm.vue`.
- **Responsabilidades**:
  - Receber `initialData?: FormData | null`, `mode?: 'create' | 'edit'` e `loading?: boolean` via `defineProps`.
  - Gerenciar o estado local reativo dos campos do formulário com `ref` e inicializar com `watch(() => props.initialData, ...)`.
  - Executar validação de frontend usando `@vuelidate/core` (`useVuelidate`) e `@vuelidate/validators` (`required`, `minLength`, `url`, etc.).
  - Emitir eventos: `defineEmits<{ (e: 'submit', payload: FormData): void; (e: 'cancel'): void; }>()`.
  - **NÃO** fazer chamadas HTTP nem injetar Stores diretamente.

### 3. Componentes Base de UI (`src/components/`)
- Componentes puros de apresentação (*Dumb Components*).
- Exemplos: `Button`, `Input`, `Select`, `MultiSelect`, `Table`, `Badge`, `Loader`, `Icon`, `Pagination`.
- **Regras**:
  - Não conhecem rotas (`useRoute`, `useRouter`).
  - Não conhecem Stores (`Pinia`).
  - Recebem dados via `defineProps` e comunicam ações via `defineEmits` ou `v-model`.

### 4. Stores (`src/stores/`)
- Implementadas com Pinia no padrão Setup Store (`defineStore('name', () => { ... })`).
- Exemplos: `projectsStore.ts`, `technologiesStore.ts`, `experiencesStore.ts`, `authStore.ts`, `profileStore.ts`.
- **Responsabilidades**:
  - Manter o estado compartilhado da entidade (`projects: Ref<Project[]>`, `totalItems: Ref<number>`).
  - Formatar dados para envio (ex: montar `FormData` quando houver upload de arquivos).
  - Invocar o respectivo Service (`projectService.saveProject(...)`).
  - Atualizar os estados locais após respostas de sucesso.

### 5. Services (`src/services/`)
- Módulos simples que exportam objetos literais contendo métodos assíncronos.
- Exemplos: `projectService.ts`, `technologyService.ts`, `experienceService.ts`, `authService.ts`.
- Retornam o payload de dados desembrulhado da `ApiResponse<T>`.

### 6. Composables (`src/composables/`)
- Lógica de estado e utilitários transversais:
  - `useLoading(initialState)`: Fornece `isLoading`, `startLoading`, `stopLoading` e o helper assíncrono `withLoading(async () => { ... })`.
  - `useToast()`: Fornece a função `showToast(type: 'success' | 'error' | 'info' | 'warning', message: string)` integrada com o componente `Toast.vue`.

---

## 🔐 Sessão e Autenticação (JWT + HttpOnly Cookie)

- O **Access Token** é mantido em memória dentro da `authStore`.
- O **Refresh Token** fica protegido em um cookie `HttpOnly` gerenciado pelo backend.
- O cliente Axios (`src/api/index.ts`) intercepta respostas com status `401 Unauthorized` e realiza o refresh automático enfileirando requisições concorrentes.
- Rotas protegidas no Vue Router utilizam `meta: { requiresAuth: true }` com navegação guardada em `router.beforeEach`.
