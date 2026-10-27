# Regras Específicas para Views (Páginas do Portfolio CMS)

No **Portfolio CMS** (`cms/`), uma View é o componente de nível de rota registrado em `src/router/index.ts`. Sua principal responsabilidade é **orquestrar** o carregamento de dados, a paginação, a busca e a composição dos componentes visuais e formulários.

---

## 1. Responsabilidades de uma View

### Uma View DEVE:
- Conectar-se com a Store correspondente da Pinia (`useProjectsStore`, `useTechnologiesStore`, etc.).
- Utilizar os composables do sistema: `useLoading` (gerenciamento de requisições assíncronas) e `useToast` (notificações para o usuário).
- Gerenciar o estado de paginação (`page`, `itemsPerPage`) e filtros (`search` com debounce, `selectedStatus`).
- Controlar abertura e fechamento de modais (`Modal.vue`) e diálogos de confirmação (`Dialog.vue`).
- Passar dados planos como props para componentes filhos e formulários.

### Uma View NÃO DEVE:
- Conter regras de validação complexas de formulários inline (extrair para `src/forms/{Entity}Form.vue`).
- Chamar o Axios ou o cliente HTTP diretamente (usar sempre a Store ou Service).
- Acumular centenas de linhas de templates estáticos ou tabelas inline desnecessárias.

---

## 2. Padrão Estrutural de uma View do CMS

Veja o exemplo canônico baseado em [`src/views/Projects.vue`](file:///Users/davidfreitas/www/portfolio/cms/src/views/Projects.vue):

```vue
<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { debounce } from 'vue-debounce';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import { useProjectsStore } from '@/stores/projectsStore';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Icon from '@/components/Icon.vue';
import Button from '@/components/Button.vue';
import InputSearch from '@/components/InputSearch.vue';
import Select from '@/components/Select.vue';
import Table from '@/components/Table.vue';
import Modal, { type ModalExpose } from '@/components/Modal.vue';
import Dialog from '@/components/Dialog.vue';
import Pagination from '@/components/Pagination.vue';
import ProjectForm, { type ProjectFormData } from '@/forms/ProjectForm.vue';
import type { Option, Project } from '@/types';

// Stores e Composables
const projectsStore = useProjectsStore();
const { isLoading, withLoading } = useLoading();
const { showToast } = useToast();

// Refs de Modal e Diálogo
const modalRef = ref<ModalExpose | null>(null);
const dialogRef = ref<{ openModal: () => void } | null>(null);

// Estado de Paginação e Filtros
const page = ref(1);
const itemsPerPage = 10;
const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());
const selectedStatus = ref<Option | null>(null);

// Carregamento de Dados
const loadProjects = async () => {
  await withLoading(() =>
    projectsStore.fetchProjects(page.value, itemsPerPage, normalizedSearch.value, selectedStatus.value?.value)
  );
};

const debouncedLoadProjects = debounce(loadProjects, '500ms');

// Watchers de Reatividade
watch(search, () => {
  page.value = 1;
  debouncedLoadProjects();
});

watch(selectedStatus, () => {
  page.value = 1;
  loadProjects();
});

watch(page, () => {
  loadProjects();
});

// Ações do Usuário
const handleOpenCreate = () => {
  modalRef.value?.openModal();
};

const handleSubmit = async (payload: ProjectFormData) => {
  await withLoading(async () => {
    await projectsStore.saveProject(payload);
    modalRef.value?.closeModal();
    showToast('success', 'Projeto salvo com sucesso!');
    await loadProjects();
  });
};

onMounted(async () => {
  await loadProjects();
});
</script>

<template>
  <Container>
    <!-- Header e Breadcrumbs -->
    <!-- Filtros: InputSearch + Select -->
    <!-- Tabela ou Listagem com Pagination -->
    <!-- Modal com ProjectForm -->
    <!-- Dialog para confirmação de exclusão -->
  </Container>
</template>
```

---

## 3. Gestão de Modais e Diálogos de Confirmação

- Para **Criação/Edição**: Utilize `Modal.vue` com `ref="modalRef"` e o tipo `ModalExpose` para chamar `modalRef.value?.openModal()` e `closeModal()`.
- Para **Exclusão/Ações Destrutivas**: Utilize `Dialog.vue` com `@confirm-action="handleConfirmDelete"`. Não realize exclusões diretas sem confirmação prévia do usuário.
