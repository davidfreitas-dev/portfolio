<script setup>
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import InputFile from '../shared/InputFile.vue';
import Textarea from '../shared/Textarea.vue';
import Toast from '../shared/Toast.vue';

const props = defineProps({
  project: {
    type: Object,
    default: () => ({
      destitle: '',
      desdescription: '',
      desimage: ''
    })
  }
});

const project = ref({ ...props.project });

const imagePreview = ref(undefined);

watch(
  () => project.value.desimage,
  (newPhoto) => {
    if (newPhoto) {
      const reader = new FileReader();

      reader.onload = (e) => {
        imagePreview.value = e.target.result;
      };
      
      reader.readAsDataURL(newPhoto);
    }
  }
);

const toastRef = ref(null);
const isLoading = ref(false);
const emit = defineEmits(['onCloseModal']);

const save = async (project) => {
  isLoading.value = true;
  
  try {
    await axios.post('/projects/save', project, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao adicionar/editar projeto');
  }
  
  isLoading.value = false;
};

const submitForm = async (event) => {
  event.preventDefault(); 
  save(project.value);
};

const deleteProject = async (projectId) => {
  try {
    await axios.delete(`/projects/delete/${projectId}`);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao deletar projeto');
  }
};

const rules = computed(() => ({
  destitle: { required },
  desdescription: { required }
}));

const v$ = useVuelidate(rules, project);

const isFormValid = computed(() => v$.value.$pending || v$.value.$invalid);
</script>

<template>
  <form @submit="submitForm">
    <div class="flex flex-col gap-3 md:w-1/2">
      <label class="font-semibold">
        Imagem
      </label>

      <img
        v-if="imagePreview"
        :src="imagePreview"
        class="h-44 w-44 rounded-lg"
      >

      <InputFile v-model="project.desimage" />
      
      <p class="mb-3 text-sm text-gray-500 dark:text-gray-300">
        SVG, PNG, JPG or GIF (MAX. 800x800px).
      </p>
    </div>
        
    <Input
      v-model="project.destitle"
      label="Nome"
      placeholder="Nome do projeto"
    />

    <Textarea 
      v-model="project.desdescription"
      label="Descrição" 
      placeholder="Descrição do projeto" 
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
        v-if="project.idproject"
        type="button"
        color="outline"
        class="mt-5 mr-3"
        :is-loading="isLoading"
        :disabled="isLoading"
        @click="deleteProject(project.idproject)"
      >
        Excluir Dados
      </Button>
    </div>
  </form>

  <Toast ref="toastRef" />
</template>
