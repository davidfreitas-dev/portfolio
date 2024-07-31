<script setup>
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import Textarea from '../shared/Textarea.vue';
import InputDate from '../shared/InputDate.vue';
import Toast from '../shared/Toast.vue';

const props = defineProps({
  experience: {
    type: Object,
    default: () => ({
      destitle: '',
      desdescription: '',
      dtstart: '',
      dtend: ''
    })
  }
});

const emit = defineEmits(['onCloseModal']);

const experience = ref({ ...props.experience });

watch(() => props.experience, (newValue) => {
  experience.value = { ...newValue };
}, { deep: true });

const rules = computed(() => ({
  destitle: { required },
  desdescription: { required },
  dtstart: { required, minLength: minLength(6) },
  dtend: { minLength: minLength(6) }
}));

const v$ = useVuelidate(rules, experience);

const isFormValid = computed(() => v$.value.$pending || v$.value.$invalid);

const toastRef = ref(null);

const isLoading = ref(false);

const save = async (experience) => {
  isLoading.value = true;

  try {
    const response = !experience.idexperience 
      ? await axios.post('/experiences/create', experience)
      : await axios.put(`/experiences/update/${experience.idexperience}`, experience) ;

    toastRef.value?.showToast(response.status, response.message);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao adicionar/editar experiência');
  }
  
  isLoading.value = false;
};

const submitForm = async (event) => {
  event.preventDefault();  
  save(experience.value);
};

const deleteExperience = async (experienceId) => {
  isLoading.value = true;

  try {
    const response = await axios.delete(`/experiences/delete/${experienceId}`);
    toastRef.value?.showToast(response.status, response.message);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao adicionar/editar experiência');
  }
  
  isLoading.value = false;
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

      <Button
        v-if="experience.idexperience"
        type="button"
        color="outline"
        class="mt-5 mr-3"
        :is-loading="isLoading"
        :disabled="isLoading"
        @click="deleteExperience(experience.idexperience)"
      >
        Excluir Dados
      </Button>
    </div>
  </form>

  <Toast ref="toastRef" />
</template>
