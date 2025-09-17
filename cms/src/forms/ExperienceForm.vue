<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import Input from '@/components/Input.vue';
import Textarea from '@/components/Textarea.vue';
import Button from '@/components/Button.vue';

export interface ExperienceFormData {
  id?: number;
  title: string;
  description: string;
  start_date: string;
  end_date: string;
}

const props = defineProps<{
  initialData?: ExperienceFormData | null;
  mode?: 'create' | 'edit';
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'submit', payload: ExperienceFormData): void;
  (e: 'cancel'): void;
}>();

const emptyForm: ExperienceFormData = {
  title: '',
  description: '',
  start_date: '',
  end_date: ''
};

const formData = ref<ExperienceFormData>({ ...emptyForm });

watch(
  () => props.initialData,
  (newVal) => {
    formData.value = newVal ? { ...newVal } : { ...emptyForm };
  },
  { immediate: true }
);

const rules = computed(() => ({
  title: { required, minLength: minLength(3) },
  description: { required },
  start_date: { required },
  end_date: {} 
}));

const v$ = useVuelidate(rules, formData);

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
    <Input
      v-model="formData.title"
      label="Título"
      placeholder="Ex: Analista de Dados, Tech Lead..."
      :error="v$.title.$dirty && v$.title.$error ? 'Informe o título (mín. 3 caracteres)' : ''"
      @blur="v$.title.$touch"
    />

    <Textarea
      v-model="formData.description"
      label="Descrição"
      placeholder="Resumo da experiência"
      :error="v$.description.$dirty && v$.description.$error ? 'Informe a descrição' : ''"
      @blur="v$.description.$touch"
    />

    <Input
      v-model="formData.start_date"
      type="date"
      label="Data de início"
      :error="v$.start_date.$dirty && v$.start_date.$error ? 'Informe a data de início' : ''"
      @blur="v$.start_date.$touch"
    />

    <Input
      v-model="formData.end_date"
      type="date"
      label="Data de término"
      :error="v$.end_date.$dirty && v$.end_date.$error ? 'Data inválida' : ''"
      @blur="v$.end_date.$touch"
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
