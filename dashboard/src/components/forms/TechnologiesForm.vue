<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import InputFile from '../shared/InputFile.vue';
import Toast from '../shared/Toast.vue';

const emit = defineEmits(['onCloseModal']);

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

const buildFormData = (technology) => {
  const formData = new FormData();
  if (technology.idtechnology) formData.append('idtechnology', technology.idtechnology);
  if (technology.desimage instanceof File) formData.append('image', technology.desimage);
  formData.append('desname', technology.desname);
  return formData;
};

const toastRef = ref(null);

const isLoading = ref(false);

const save = async (technology) => {
  isLoading.value = true;

  const formData = buildFormData(technology);

  try {
    const response = axios.post('/technologies/save', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      }
    });

    await nextTick();

    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, error.data?.message);
  }
  
  isLoading.value = false;
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
    </div>
  </form>

  <Toast ref="toastRef" />
</template>
