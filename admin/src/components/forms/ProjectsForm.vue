<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import InputFile from '../shared/InputFile.vue';
import Textarea from '../shared/Textarea.vue';
import MultiSelect from '../shared/MultiSelect.vue';
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

const deleteProject = async (projectId) => {
  try {
    await axios.delete(`/projects/delete/${projectId}`);
    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao deletar projeto');
  }
};

const techs = ref([]);

const formatTechFields = async (data) => {
  return data.map(tech => ({
    id: tech.idtechnology,
    name: tech.desname
  }));
};

const getTechs = async () => {
  try {
    const response = await axios.get('/technologies');
    techs.value = await formatTechFields(response.data) ?? [];
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao texnologias');
  }
};

onMounted(async () => {
  await getTechs();
});

const selectedTechs = ref([]);

const handleSelectChange = (newselectedTechs) => {
  selectedTechs.value = newselectedTechs;
};

const submitForm = async (event) => {
  event.preventDefault(); 

  project.value.technologies = selectedTechs.value
    .map(tech => tech.id)
    .join(', ');

  save(project.value);
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
        v-if="imagePreview || project.desimage"
        :src="imagePreview || project.desimage"
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

    <MultiSelect
      label="Tecnologias"
      :options="techs"
      :selected="selectedTechs"
      @handle-select-change="handleSelectChange"
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
