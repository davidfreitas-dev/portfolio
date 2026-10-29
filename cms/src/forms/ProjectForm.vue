<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, url } from '@vuelidate/validators';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';
import InputFile from '@/components/InputFile.vue';
import Textarea from '@/components/Textarea.vue';
import Select from '@/components/Select.vue';
import InputTag from '@/components/InputTag.vue';

export interface ProjectFormData {
  id?: number;
  title: string;
  description: string;
  link?: string;
  image?: File | string | null;
  is_active?: number;
  technologies: string[];
  remove_image?: boolean;
}

const props = defineProps<{
  initialData?: ProjectFormData | null;
  mode?: 'create' | 'edit';
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'submit', payload: ProjectFormData): void;
  (e: 'cancel'): void;
}>();

const emptyForm: ProjectFormData = {
  title: '',
  description: '',
  link: '',
  image: null,
  is_active: 1,
  technologies: []
};

const formData = ref<ProjectFormData>({ ...emptyForm });
const removeImage = ref(false);

// Validações
const rules = computed(() => ({
  title: { required, minLength: minLength(3) },
  description: { required, minLength: minLength(5) },
  link: { url },
  technologies: {},
  is_active: { required },
  image: {}
}));

const state = computed(() => ({
  title: formData.value.title,
  description: formData.value.description,
  link: formData.value.link,
  image: formData.value.image,
  is_active: formData.value.is_active,
  technologies: formData.value.technologies
}));

const v$ = useVuelidate(rules, state);

// Inicializa o formulário com dados existentes (edição)
watch(
  () => props.initialData,
  (newVal) => {
    removeImage.value = false;
    formData.value = newVal ? { ...newVal, technologies: [...(newVal.technologies || [])] } : { ...emptyForm };
    v$.value.$reset();
  },
  { immediate: true }
);

watch(
  () => formData.value.image,
  (newVal, oldVal) => {
    if (newVal instanceof File) {
      removeImage.value = false;
    } else if (newVal === null && oldVal !== undefined && oldVal !== null) {
      removeImage.value = true;
    }
  }
);

const handleImageClear = () => {
  formData.value.image = null;
  removeImage.value = true;
};

// Status do projeto
const selectedStatus = ref<{ label: string; value: number }>(
  formData.value.is_active !== undefined
    ? { label: formData.value.is_active ? 'Ativo' : 'Inativo', value: formData.value.is_active }
    : { label: 'Inativo', value: 0 }
);

watch(selectedStatus, (newVal) => {
  formData.value.is_active = newVal.value;
});

watch(
  () => formData.value.is_active,
  (newVal) => {
    selectedStatus.value = newVal !== undefined
      ? { label: newVal ? 'Ativo' : 'Inativo', value: newVal }
      : { label: 'Inativo', value: 0 };
  }
);

// Submissão do formulário
const submitForm = async () => {
  const isValid = await v$.value.$validate();
  if (!isValid) return;
  emit('submit', {
    ...formData.value,
    remove_image: removeImage.value
  });
  v$.value.$reset();
};

// Cancelar e resetar
const handleCancel = () => {
  emit('cancel');
  formData.value = { ...emptyForm };
  selectedStatus.value = { label: 'Ativo', value: 1 };
  removeImage.value = false;
  v$.value.$reset();
};
</script>

<template>
  <form class="flex flex-col flex-1 min-h-0 overflow-hidden" @submit.prevent="submitForm">
    <div class="form-body flex-1 min-h-0 overflow-y-auto p-6 flex flex-col gap-4">
      <InputFile
        v-model="formData.image"
        label="Imagem do Projeto"
        image-path="projects"
        @clear="handleImageClear"
      />

      <Input
        v-model="formData.title"
        label="Título"
        placeholder="Ex: Meu Portfólio em Vue 3"
        :error="v$.title.$dirty && v$.title.$error ? 'Informe um título válido (mín. 3 caracteres)' : ''"
        @blur="v$.title.$touch"
      />

      <Textarea
        v-model="formData.description"
        label="Descrição"
        placeholder="Breve descrição do projeto..."
        :error="v$.description.$dirty && v$.description.$error ? 'Informe uma descrição válida (mín. 5 caracteres)' : ''"
        @blur="v$.description.$touch"
      />

      <Input
        v-model="formData.link"
        label="Link do Projeto"
        placeholder="https://meuprojeto.com"
        :error="v$.link.$dirty && v$.link.$error ? 'Informe um link válido' : ''"
        @blur="v$.link.$touch"
      />

      <InputTag
        v-model="formData.technologies"
        label="Tecnologias"
        placeholder="Digite a tecnologia e pressione Enter (ex: Vue.js, PHP, Docker)..."
      />

      <Select
        v-model="selectedStatus"
        :options="[
          { label: 'Ativo', value: 1 },
          { label: 'Inativo', value: 0 }
        ]"
        label="Status"
        :error="v$.is_active.$dirty && v$.is_active.$error ? 'O status do projeto é obrigatório' : ''"
      />
    </div>

    <div class="modal-footer flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 shrink-0">
      <Button
        type="button"
        variant="fill"
        color="secondary"
        @click="handleCancel"
      >
        Cancelar
      </Button>
      <Button
        type="submit"
        :disabled="props.loading"
        :is-loading="props.loading"
      >
        {{ props.mode === 'edit' ? 'Confirmar' : 'Adicionar' }}
      </Button>
    </div>
  </form>
</template>

<style scoped>
.form-body {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.form-body::-webkit-scrollbar {
  width: 6px;
  background-color: transparent;
}

.form-body::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 9999px;
}

:global(.dark) .form-body::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>
