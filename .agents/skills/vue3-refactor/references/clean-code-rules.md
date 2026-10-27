# Checklist de Clean Code para Vue 3 (Portfolio CMS)

## 1. Responsabilidade Única (SRP)
- **Sintomas de sobrecarga**: Arquivo com > 250-300 linhas, misturando chamadas de API, regras de validação pesadas, paginação e marcação visual complexa.
- **Solução**:
  - Extraia a lógica de formulário para `src/forms/{Entity}Form.vue`.
  - Extraia modais e confirmações para o padrão `Modal.vue` / `Dialog.vue`.
  - Mova regras de filtragem complexas para `computed` ou composables.

## 2. Views Orquestram, Forms Validam, Componentes Apresentam
- **Views (`src/views/`)**: Conectam Stores da Pinia, rotas (`useRoute`, `useRouter`), orquestram paginação, busca e abrem modais.
- **Forms (`src/forms/`)**: Recebem dados iniciais, validam com `@vuelidate/core` e emitem `@submit` com o payload pronto.
- **Componentes (`src/components/`)**: Apenas recebem props e disparam eventos. Nunca acessam o Axios, rotas ou Stores diretamente.

## 3. Lógica Fora do Template
Se uma expressão condicional demorar mais de um segundo para ser lida, extraia para uma propriedade `computed`.
```vue
<!-- Evitar -->
<span v-if="projects && projects.filter(p => p.is_active).length > 0">

<!-- Recomendado -->
<span v-if="hasActiveProjects">
```

## 4. Composables por Funcionalidade
- Nomes reveladores de intenção (`useLoading`, `useToast`, `useProjectFilters`).
- Retorne propriedades reativas claras e seguras para desestruturação.
- Reutilize os composables existentes do projeto:
  - `useLoading()`: `const { isLoading, withLoading } = useLoading();`
  - `useToast()`: `const { showToast } = useToast();`

## 5. Convenção de Nomes
- **Handlers de evento**: `handleSubmit`, `handleCancel`, `handleOpenEdit`, `handleDelete`.
- **Booleans**: `isLoading`, `isActive`, `hasError`, `isSubmitting`.
- **Zero nomes genéricos**: Evite `data`, `item`, `res`, `tmp`, `val`. Seja descritivo (`project`, `selectedStatus`, `projectPayload`).

## 6. Funções Pequenas em um Único Nível de Abstração
A função principal deve ser legível como um roteiro passo a passo:
```typescript
async function handleSubmit(payload: ProjectFormData) {
  await withLoading(async () => {
    await projectsStore.saveProject(payload);
    modalRef.value?.closeModal();
    showToast('success', 'Projeto salvo com sucesso!');
    await loadProjects();
  });
}
```

## 7. Props Down, Events Up
- Nunca mute props diretamente.
- Para formulários, inicialize um estado reativo local (`formData = ref({ ...initialData })`) usando `watch` com `{ immediate: true }`.

## 8. Tipagem Estrita nas Fronteiras (TypeScript)
- Todas as props e emits devem ser estritamente tipadas com interfaces em `src/types/`.
- Proibido o uso de `any` ou `unknown`.

## 9. Reuso dos Componentes do Design System
Substitua sempre tags nativas pelos equivalentes do CMS:
- `<button>` → `<Button variant="fill" color="primary">`
- `<input>` → `<Input v-model="..." label="..." />`
- `<select>` → `<Select v-model="..." :options="..." />`
- Ícones → `<Icon name="..." />`

## 10. Limpeza de Efeitos Colaterais e Código Morto
- Todo `watch`, listener de evento global ou timer precisa de encerramento (`onUnmounted`).
- Elimine `console.log`, comentários óbvios e imports não utilizados.
