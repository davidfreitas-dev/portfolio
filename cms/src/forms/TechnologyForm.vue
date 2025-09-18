<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';
import InputFile from '@/components/InputFile.vue';

export interface TechnologyFormData {
  id?: number;
  name: string;
  image?: File | string | null;
}

const props = defineProps<{
  initialData?: TechnologyFormData | null;
  mode?: 'create' | 'edit';
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'submit', payload: TechnologyFormData): void;
  (e: 'cancel'): void;
}>();

const emptyForm: TechnologyFormData = {
  name: '',
  image: null
};

const formData = ref<TechnologyFormData>({ ...emptyForm });

watch(
  () => props.initialData,
  (newVal) => {
    formData.value = newVal ? { ...newVal } : { ...emptyForm };
  },
  { immediate: true }
);

const rules = computed(() => ({
  name: { required, minLength: minLength(2) },
  image: {}
}));

const state = computed(() => ({
  name: formData.value.name,
  image: formData.value.image
}));

const v$ = useVuelidate(rules, state);

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
      label="Imagem"
      image-path="technologies"
    />

    <Input
      v-model="formData.name"
      label="Nome da tecnologia"
      placeholder="Ex: Vue.js, Laravel, Docker..."
      :error="v$.name.$dirty && v$.name.$error ? 'Informe o nome da tecnologia (mín. 2 caracteres)' : ''"
      @blur="v$.name.$touch"
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
        :disabled="loading"
        :is-loading="loading"
      >
        {{ mode === 'edit' ? 'Confirmar' : 'Adicionar' }}
      </Button>
    </div>
  </form>
</template>
