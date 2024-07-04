<script setup>
import { ref, computed } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import Textarea from '../shared/Textarea.vue';
import InputDate from '../shared/InputDate.vue';

const isLoading = ref(false);
const experience = ref({
  destitle: '',
  desdescription: '',
  dtstart: '',
  dtend: ''
});

const rules = computed(() => {
  return {
    destitle: { required },
    desdescription: { required },
    dtstart: { required, minLength: minLength(6) },
    dtend: { required, minLength: minLength(6) }
  };
});

const v$ = useVuelidate(rules, experience);

const isFormValid = computed(() => {
  return v$.value.$pending || v$.value.$invalid;
});

const submitForm = async (event) => {
  event.preventDefault();

  // Enviar os dados
  // ...
};
</script>

<template>
  <form @submit="submitForm">
    <Input
      v-model="experience.destitle"
      label="Título"
      placeholder="Título da experiência"
    />

    <Textarea 
      v-model="experience.desdescription"
      label="Descrição" 
      placeholder="Descrição da experiência" 
    />

    <InputDate
      v-model="experience.dtstart"
      label="Data de início"
      date-format="MM/YYYY"
    />

    <InputDate
      v-model="experience.dtend"
      label="Data do fim"
      date-format="MM/YYYY"
    />

    <div class="flex flex-row-reverse">
      <Button
        class="mt-5"
        :is-loading="isLoading"
        :disabled="isLoading || isFormValid"
      >
        Salvar Dados
      </Button>
    </div>
  </form>
</template>
