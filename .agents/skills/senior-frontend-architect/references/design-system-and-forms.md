# Design System, Componentes e Formulários (Portfolio CMS)

O **Portfolio CMS** possui um Design System customizado construído com **TailwindCSS 4**, **Headless UI** e ícones **Lucide** (`lucide-vue-next`).

Toda a implementação de interface deve reutilizar estritamente os componentes de `src/components/` e seguir os padrões de formulários em `src/forms/`. O catálogo visual completo pode ser inspecionado em [`src/views/DesignSystem.vue`](file:///Users/davidfreitas/www/portfolio/cms/src/views/DesignSystem.vue).

---

## 🎨 Paleta de Cores e Tema

- **Primary**:
  - `bg-primary-dark` (#1e3a8a ou escala escura)
  - `bg-primary-default` (cor de destaque principal)
  - `bg-primary-light`
  - `bg-primary-bg` (fundo suave para badges e hover)
- **Neutros / Grays**:
  - `gray-900`, `gray-800`, `gray-700`, `gray-600`, `gray-500`, `gray-400`, `gray-300`, `gray-200`, `gray-100`, `white`.
- **Feedback (Status)**:
  - `success` / `success-dark` (verde para ativos e sucesso)
  - `warning` / `warning-dark` (amarelo/laranja para alertas)
  - `danger` / `danger-dark` (vermelho para inativos e erros)
- **Dark Mode**:
  - Suportado via classe `dark` no root HTML. Use sempre modificadores como `dark:bg-gray-800`, `dark:text-gray-100`, `dark:border-gray-700`.

---

## 🧩 Catálogo de Componentes (`src/components/`)

### 1. `Button.vue`
Botão versátil com suporte a loading integrado e slots para ícones.
```vue
<script setup lang="ts">
import Button from '@/components/Button.vue';
import Icon from '@/components/Icon.vue';
</script>

<template>
  <!-- Exemplo Preenchido com Ícone -->
  <Button variant="fill" color="primary" size="medium" :is-loading="isLoading">
    <template #left-icon>
      <Icon name="add" class="w-5 h-5" />
    </template>
    Novo Projeto
  </Button>

  <!-- Exemplo Outline Secundário -->
  <Button variant="outline" color="secondary" size="small" @click="handleCancel">
    Cancelar
  </Button>
</template>
```
- **Props**:
  - `variant`: `'fill'` | `'outline'` | `'link'` (default: `'fill'`)
  - `color`: `'primary'` | `'secondary'` (default: `'primary'`)
  - `size`: `'large'` | `'medium'` | `'small'` | `'full'` (default: `'medium'`)
  - `isLoading`: `boolean` (default: `false` — exibe automaticamente o `<Loader />`)
  - `disabled`: `boolean` (default: `false`)
- **Slots**: `#left-icon`, `#right-icon`, default (texto do botão).

---

### 2. Campos de Formulário (`Input`, `Select`, `Textarea`, etc.)

#### `Input.vue`
```vue
<Input
  v-model="formData.title"
  label="Título *"
  placeholder="Nome do projeto..."
  :error="v$.title.$errors[0]?.$message as string"
  @blur="v$.title.$touch"
/>
```
- **Props**: `modelValue`, `label`, `placeholder`, `disabled`, `error`, `type`.

#### `InputSearch.vue`
Campo otimizado para filtros de busca:
```vue
<InputSearch v-model="search" placeholder="Buscar projetos..." />
```

#### `InputDate.vue`
Campo de data integrado com datepicker:
```vue
<InputDate
  v-model="formData.start_date"
  label="Data de Início *"
  :error="v$.start_date.$errors[0]?.$message as string"
/>
```

#### `InputFile.vue`
Campo de upload de imagem/arquivo com preview:
```vue
<InputFile
  v-model="formData.image"
  label="Imagem de Capa"
  accept="image/png, image/jpeg, image/webp"
/>
```

#### `Select.vue` e `MultiSelect.vue`
```vue
<Select
  v-model="selectedStatus"
  :options="statusOptions"
  label="Status"
  :error="errorMsg"
/>

<MultiSelect
  v-model="formData.technologies"
  :options="technologiesOptions"
  label="Tecnologias"
/>
```

#### `Textarea.vue`
```vue
<Textarea
  v-model="formData.description"
  label="Descrição *"
  placeholder="Descreva o projeto..."
  :error="v$.description.$errors[0]?.$message as string"
/>
```

#### `Switch.vue` e `Checkbox.vue`
```vue
<Switch v-model="isActive" />
<Checkbox v-model="isSelected" label="Marcar como atual" />
```

---

### 3. Modais e Diálogos (`Modal.vue` e `Dialog.vue`)

#### `Modal.vue` (Para formulários e conteúdos grandes)
Utiliza Headless UI `Dialog` com transições suaves e expõe `openModal()` e `closeModal()` através de `ModalExpose`.
```vue
<script setup lang="ts">
import { ref } from 'vue';
import Modal, { type ModalExpose } from '@/components/Modal.vue';
import ProjectForm, { type ProjectFormData } from '@/forms/ProjectForm.vue';

const modalRef = ref<ModalExpose | null>(null);

const handleOpenCreate = () => {
  modalRef.value?.openModal();
};

const handleModalClose = () => {
  modalRef.value?.closeModal();
};
</script>

<template>
  <Modal ref="modalRef" title="Novo Projeto" align="center" @on-modal-close="onClosed">
    <ProjectForm
      :loading="isSubmitting"
      @submit="handleSubmit"
      @cancel="handleModalClose"
    />
  </Modal>
</template>
```

#### `Dialog.vue` (Para confirmações e alertas)
```vue
<script setup lang="ts">
import { ref } from 'vue';
import Dialog from '@/components/Dialog.vue';

const dialogRef = ref<{ openModal: () => void } | null>(null);

const handleAskDelete = () => {
  dialogRef.value?.openModal();
};

const handleConfirmDelete = async () => {
  await projectsStore.deleteProject(targetId.value);
  showToast('success', 'Projeto excluído com sucesso.');
};
</script>

<template>
  <Dialog
    ref="dialogRef"
    header="Excluir Projeto"
    message="Tem certeza de que deseja remover este projeto? Esta ação não pode ser desfeita."
    confirm-text="Excluir"
    @confirm-action="handleConfirmDelete"
  />
</template>
```

---

### 4. `Icon.vue`
Mapeia ícones pelo nome para o catálogo da biblioteca **Lucide**:
```vue
<Icon name="add" class="w-5 h-5" />        <!-- Lucide Plus -->
<Icon name="edit" class="w-5 h-5" />       <!-- Lucide Pencil -->
<Icon name="delete" class="w-5 h-5" />     <!-- Lucide Trash -->
<Icon name="search" class="w-5 h-5" />     <!-- Lucide Search -->
<Icon name="close" class="w-5 h-5" />      <!-- Lucide X -->
<Icon name="check" class="w-5 h-5" />      <!-- Lucide Check -->
```

---

### 5. `Pagination.vue`
Componente de navegação paginada que atualiza a página via `v-model`:
```vue
<Pagination
  v-model="page"
  :total-items="projectsStore.totalItems"
  :items-per-page="itemsPerPage"
/>
```

---

## 📝 Formulários com Vuelidate (`src/forms/`)

Todos os formulários devem residir em `src/forms/{Entity}Form.vue` e seguir esta estrutura rigorosa:

```vue
<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';

export interface FormData {
  id?: number;
  title: string;
}

const props = defineProps<{
  initialData?: FormData | null;
  mode?: 'create' | 'edit';
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'submit', payload: FormData): void;
  (e: 'cancel'): void;
}>();

const emptyForm: FormData = { title: '' };
const formData = ref<FormData>({ ...emptyForm });

watch(
  () => props.initialData,
  (newVal) => {
    formData.value = newVal ? { ...newVal } : { ...emptyForm };
  },
  { immediate: true }
);

const rules = computed(() => ({
  title: { required, minLength: minLength(3) },
}));

const v$ = useVuelidate(rules, formData);

const handleSubmit = async () => {
  const isValid = await v$.value.$validate();
  if (!isValid) return;
  emit('submit', formData.value);
};
</script>
```

---

## 🔔 Notificações e Loading nos Composables

### `useToast`
```typescript
import { useToast } from '@/composables/useToast';

const { showToast } = useToast();

showToast('success', 'Operação realizada com sucesso!');
showToast('error', 'Ocorreu um erro ao processar os dados.');
showToast('warning', 'Atenção aos dados informados.');
showToast('info', 'Carregando informações...');
```

### `useLoading`
```typescript
import { useLoading } from '@/composables/useLoading';

const { isLoading, withLoading } = useLoading();

// Execução segura: ativa o loader e garante o encerramento no finally
await withLoading(async () => {
  await projectsStore.fetchProjects(page.value, limit, search.value);
});
```
