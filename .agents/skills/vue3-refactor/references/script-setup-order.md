# Organização e Ordem no `<script setup lang="ts">` (Portfolio CMS)

## Estrutura de Blocos no SFC (.vue)
```vue
<script setup lang="ts">
// Lógica do componente
</script>

<template>
  <!-- Template com classes Tailwind 4 -->
</template>

<style scoped>
/* Apenas quando estritamente necessário */
</style>
```

---

## Ordem Rigorosa Dentro do `<script setup>`

1. **Imports de Bibliotecas Externas**:
   `import { ref, computed, onMounted, watch } from 'vue';`
   `import { useVuelidate } from '@vuelidate/core';`
   `import { debounce } from 'vue-debounce';`

2. **Imports Internos do Projeto**:
   - Composables: `import { useLoading } from '@/composables/useLoading';`
   - Stores: `import { useProjectsStore } from '@/stores/projectsStore';`
   - Componentes Base: `import Button from '@/components/Button.vue';`
   - Forms / Subcomponentes: `import ProjectForm from '@/forms/ProjectForm.vue';`
   - Tipos e Interfaces: `import type { Project, Option } from '@/types';`

3. **Declaração de Macros de Compilador**:
   - `defineOptions({ inheritAttrs: false });`
   - `defineProps<{ ... }>();`
   - `defineEmits<{ ... }>();`
   - `defineModel<...>();`

4. **Instanciação de Stores e Composables**:
   ```typescript
   const projectsStore = useProjectsStore();
   const { isLoading, withLoading } = useLoading();
   const { showToast } = useToast();
   ```

5. **Estado Reativo Local (`ref`)**:
   ```typescript
   const page = ref(1);
   const search = ref('');
   const modalRef = ref<ModalExpose | null>(null);
   ```

6. **Propriedades Computadas (`computed`)**:
   ```typescript
   const normalizedSearch = computed(() => search.value.trim().toLowerCase());
   ```

7. **Watchers (`watch`, `watchEffect`)**:
   ```typescript
   watch(search, () => {
     page.value = 1;
     debouncedLoad();
   });
   ```

8. **Funções e Handlers de Ação**:
   ```typescript
   const handleOpenCreate = () => { ... };
   const handleSubmit = async (payload: ProjectFormData) => { ... };
   ```

9. **Hooks de Ciclo de Vida**:
   ```typescript
   onMounted(async () => {
     await loadProjects();
   });
   ```

10. **Exposição de Métodos Públicos (`defineExpose`)**:
    Apenas se o componente for consumido via template ref pelo pai (ex: `Modal.vue` expondo `openModal` e `closeModal`).
