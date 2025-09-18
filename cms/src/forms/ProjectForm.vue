<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';
import InputFile from '@/components/InputFile.vue';
import Textarea from '@/components/Textarea.vue';
import Select from '@/components/Select.vue';

export interface ProjectFormData {
  id?: number;
  title: string;
  description: string;
  link?: string;
  image?: File | string | null;
  is_active?: number;
  technologies: number[];
}

const props = defineProps<{
  initialData?: ProjectFormData | null;
  mode?: 'create' | 'edit';
  loading?: boolean;
  technologiesOptions: { label: string; value: number }[];
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

watch(
  () => props.initialData,
  (newVal) => {
    formData.value = newVal ? { ...newVal } : { ...emptyForm };
  },
  { immediate: true }
);

const rules = computed(() => ({
  title: { required, minLength: minLength(3) },
  description: { required, minLength: minLength(5) },
  link: {},
  image: {},
  is_active: {},
  technologies: {}
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

const selectedStatus = ref<{ label: string; value: number } | null>(
  formData.value.is_active !== undefined
    ? { label: formData.value.is_active ? 'Ativo' : 'Inativo', value: formData.value.is_active ? 1 : 0 }
    : null
);

watch(selectedStatus, (newVal) => {
  formData.value.is_active = newVal?.value ?? 0;
});

watch(
  () => formData.value.is_active,
  (newVal) => {
    selectedStatus.value = newVal !== undefined
      ? { label: newVal ? 'Ativo' : 'Inativo', value: newVal ? 1 : 0 }
      : null;
  }
);

const submitForm = async () => {
  const isValid = await v$.value.$validate();
  if (!isValid) return;
  emit('submit', formData.value);
};

const handleCancel = () => {
  emit('cancel');
  formData.value = { ...emptyForm };
  v$.value.$reset();
};
</script>

<template>
  <form class="flex flex-col gap-4" @submit.prevent="submitForm">
    <InputFile
      v-model="formData.image"
      label="Imagem do Projeto"
      image-path="projects"
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

    <div class="flex justify-end gap-3 mt-4">
      <Button
        type="button"
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
