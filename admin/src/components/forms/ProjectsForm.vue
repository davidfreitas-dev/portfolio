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

const emit = defineEmits(['onCloseModal']);

const project = ref({ ...props.project });

watch(() => props.project, (newValue) => {
  project.value = { ...newValue };
}, { deep: true });

const rules = computed(() => ({
  destitle: { required },
  desdescription: { required }
}));

const v$ = useVuelidate(rules, project);

const isFormValid = computed(() => v$.value.$pending || v$.value.$invalid);

const toastRef = ref(null);

const isLoading = ref(false);

const save = async (project) => {
  isLoading.value = true;

  try {
    const response = !project.idproject 
      ? await axios.post('/projects/create', project)
      : await axios.put(`/projects/update/${project.idproject}`, project);

    toastRef.value?.showToast(response.status, response.message);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.response?.status, 'Falha ao adicionar/editar experiência');
  }
  
  isLoading.value = false;
};

const submitForm = async (event) => {
  event.preventDefault();  
  save(project.value);
};

const deleteProject = async (projectId) => {
  isLoading.value = true;

  try {
    const response = await axios.delete(`/projects/delete/${projectId}`);
    toastRef.value?.showToast(response.status, response.message);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.response?.status, 'Falha ao adicionar/editar experiência');
  }
  
  isLoading.value = false;
};
</script>

<template>
  <form @submit="submitForm">
    <InputFile v-model="project.photo" />
      
    <p class="my-3 text-sm text-gray-500 dark:text-gray-300">
      SVG, PNG, JPG or GIF (MAX. 800x800px).
    </p>
        
    <Input
      v-model="project.destitle"
      label="Título"
      placeholder="Título da experiência"
    />

    <Textarea 
      v-model="project.desdescription"
      label="Descrição" 
      placeholder="Descrição da experiência" 
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
