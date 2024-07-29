<script setup>
import { ref, computed } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import Textarea from '../shared/Textarea.vue';
import InputDate from '../shared/InputDate.vue';
import Toast from '../shared/Toast.vue';
import dayjs from 'dayjs';
import 'dayjs/locale/pt-br';

const emit = defineEmits(['onCloseModal']);

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

const formatDate = async (dateStr) => {
  const month = dateStr.slice(0, 2);
  const year = dateStr.slice(2, 6);
  return dayjs(`${year}-${month}-01`).locale('pt-br').format('MMM YYYY').toUpperCase();
};

const toastRef = ref(null);

const save = async (experience) => {
  try {
    const response = await axios.post('/experiences/create', experience);
    toastRef.value?.showToast(response.status, response.message);
    emit('onCloseModal');
  } catch (error) {
    toastRef.value?.showToast(error.data.status, error.data.message);
  }
};

const submitForm = async (event) => {
  event.preventDefault();
  
  const experienceFormatted = {
    ...experience.value,
    dtstart: await formatDate(experience.value.dtstart),
    dtend: await formatDate(experience.value.dtend),
  };
  
  save(experienceFormatted);
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

  <Toast ref="toastRef" />
</template>
