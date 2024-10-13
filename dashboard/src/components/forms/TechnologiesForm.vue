<script setup>
import { ref, watch, computed } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import InputFile from '../shared/InputFile.vue';
import Toast from '../shared/Toast.vue';

const props = defineProps({
  technology: {
    type: Object,
    default: () => ({
      desname: '',
      desimage: '',
    })
  }
});

const technology = ref({ ...props.technology });

const createTechnology = (requestData) => {
  return axios.post('/technologies/create', requestData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
};

const updateTechnology = (idtechnology, requestData) => {
  return axios.post(`/technologies/update/${idtechnology}`, requestData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
};

const buildFormData = (technology) => {
  const formData = new FormData();
  formData.append('desname', technology.desname);
  if (technology.desimage instanceof File) formData.append('image', technology.desimage);
  return formData;
};

const toastRef = ref(null);
const isLoading = ref(false);
const emit = defineEmits(['onCloseModal']);

const save = async (technology) => {
  isLoading.value = true;

  const { idtechnology } = technology;

  const formData = buildFormData(technology);

  try {
    const response = idtechnology 
      ? await updateTechnology(idtechnology, formData) 
      : await createTechnology(formData);

    toastRef.value?.showToast(response.status, response.message);

    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, error.data?.message);
  }
  
  isLoading.value = false;
};

const deleteTechnology = async (technologyId) => {
  try {
    await axios.delete(`/technologies/delete/${technologyId}`);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao deletar tecnologia');
  }
};

const imagePreview = ref(undefined);

watch(
  () => technology.value.desimage,
  (newImage) => {
    if (newImage && newImage instanceof File) {
      const reader = new FileReader();

      reader.onload = (e) => {
        imagePreview.value = e.target.result;
      };
      
      reader.readAsDataURL(newImage);
    }
  }
);

const submitForm = async (event) => {
  event.preventDefault();  
  save(technology.value);
};

const rules = computed(() => ({
  desname: { required }
}));

const v$ = useVuelidate(rules, technology);

const isFormValid = computed(() => v$.value.$pending || v$.value.$invalid);
</script>

<template>
  <form @submit="submitForm">
    <div class="flex flex-col gap-3 md:w-1/2">
      <label class="font-semibold">
        Imagem
      </label>

      <img
        v-if="imagePreview || technology.desimage"
        :src="imagePreview || technology.desimage"
        class="h-44 w-44 rounded-lg"
      >

      <InputFile v-model="technology.desimage" />
      
      <p class="mb-3 text-sm text-gray-500 dark:text-gray-300">
        SVG, PNG, JPG or GIF (MAX. 800x800px).
      </p>
    </div>

    <Input
      v-model="technology.desname"
      label="Nome"
      placeholder="Nome da tecnologia"
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
        v-if="technology.idtechnology"
        type="button"
        color="secondary"
        class="mt-5 mr-3"
        :disabled="isLoading"
        @click="deleteTechnology(technology.idtechnology)"
      >
        Excluir Dados
      </Button>
    </div>
  </form>

  <Toast ref="toastRef" />
</template>
