# Vue 3: Melhores Práticas e Tailwind 4 (Portfolio CMS)

Diretrizes arquiteturais para componentes, reatividade e estilo no **Portfolio CMS** (`cms/`), alinhadas com Vue 3.5+, TypeScript e TailwindCSS 4.

---

## 1. Estrutura do SFC (Single-File Component)

Mantenha as seções do arquivo estritamente nesta ordem padrão:
```vue
<script setup lang="ts">
// Lógica reativa, props, emits e imports
</script>

<template>
  <!-- Marcação declarativa com TailwindCSS 4 -->
</template>

<style scoped>
/* Apenas quando estritamente indispensável (preferir sempre Tailwind) */
</style>
```

### Ordem Interna no `<script setup lang="ts">`:
1. **Imports externos**: Vue (`ref`, `computed`, `watch`, `onMounted`), pacotes (`@vuelidate/core`, `vue-debounce`).
2. **Imports internos**: Composables (`@/composables/useLoading`), Stores (`@/stores/projectsStore`), Componentes (`@/components/Button.vue`), Tipos (`@/types`).
3. **Macros**: `defineProps`, `defineEmits`, `defineModel`, `defineOptions`.
4. **Instanciação de Stores e Composables**: `const projectsStore = useProjectsStore()`, `const { isLoading, withLoading } = useLoading()`.
5. **Estado Reativo Local**: `ref(...)`.
6. **Propriedades Computadas**: `computed(...)`.
7. **Watchers**: `watch(...)`.
8. **Funções e Handlers**: `handleSubmit`, `handleOpenEdit`, `handleDelete`.
9. **Hooks de Ciclo de Vida**: `onMounted(...)`, `onUnmounted(...)`.
10. **Exposição (se aplicável)**: `defineExpose(...)`.

---

## 2. Reatividade e TypeScript

- **Zero `any`**: Tipagem 100% explícita. Importe tipos das pastas dedicadas em `src/types/` (`Option`, `Project`, `Technology`, etc.).
- **Desestruturação Segura de Props**: No Vue 3.5+, a desestruturação de `defineProps` preserva a reatividade nativa pelo compilador.
  ```typescript
  const { initialData, mode = 'create' } = defineProps<{
    initialData?: ProjectFormData | null;
    mode?: 'create' | 'edit';
  }>();
  ```
- **Lógica Fora do Template**: Nunca insira cálculos matemáticos, filtros encadeados ou validações pesadas dentro do `<template>`. Extraia para `computed`:
  ```vue
  <!-- Incorreto -->
  <span v-if="projects.filter(p => p.is_active).length > 0">...</span>

  <!-- Correto -->
  <span v-if="hasActiveProjects">...</span>
  ```
- **Prefira `ref()` sobre `reactive()`**: Garante previsibilidade na reatribuição e evita perda de reatividade em desestruturações.

---

## 3. Divisão de Responsabilidades e Componentização

- **Smart Views (`src/views/`)**:
  - Ponto de conexão com Pinia e rotas.
  - Mantém estado de paginação (`page`), filtros de status e busca textual com debounce.
  - Orquestra modais (`Modal`, `Dialog`) e renderiza `<Table />` ou listas.
- **Forms (`src/forms/`)**:
  - Isolados da View pai para manter o código limpo e permitir reuso (ex: criar vs editar).
  - Recebem `initialData`, validam com Vuelidate e emitem `@submit` com o payload pronto.
- **Dumb Components (`src/components/`)**:
  - Totalmente reutilizáveis e desacoplados de regras de negócio.
  - Apenas recebem props e emitem eventos.

---

## 4. Estilização com TailwindCSS 4

- **Classes Utilitárias**: Utilize sempre as classes utilitárias modernas do Tailwind 4. Evite usar colchetes arbitrários desnecessários quando houver classes padrão do Tailwind ou do tema (`bg-primary-default`, `bg-primary-bg`, `text-gray-700`, `dark:text-gray-100`).
- **Suporte a Dark Mode**: Todo componente deve ser desenhado pensando em Light e Dark mode via modificadores `dark:`.
  ```html
  <div class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-100 border border-gray-200 dark:border-gray-700">
  ```
- **Transições e Feedback Visual**: Use transições nativas (`transition-colors duration-200 ease-in-out`, `active:scale-95`) para proporcionar excelente feedback tátil.

---

## 5. Performance e Experiência do Usuário

- **Buscas com Debounce**: Em campos de busca textual, sempre utilize debounce (ex: `vue-debounce` com 300ms a 500ms) para não disparar requisições em excesso ao backend.
- **Tratamento de Estados**:
  - **Loading**: Use `<Loader />` ou passe `:is-loading="isLoading"` para os botões durante chamadas assíncronas.
  - **Empty**: Exiba mensagens amigáveis na tabela quando nenhum registro for encontrado para os filtros selecionados.
  - **Erro**: Notifique com `showToast('error', ...)` sem travar a interface.
