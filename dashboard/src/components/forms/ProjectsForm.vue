<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import InputFile from '../shared/InputFile.vue';
import Textarea from '../shared/Textarea.vue';
import MultiSelect from '../shared/MultiSelect.vue';
import Switch from '../shared/Switch.vue';
import Toast from '../shared/Toast.vue';

const props = defineProps({
  project: {
    type: Object,
    default: () => ({
      destitle: '',
      desdescription: '',
      desimage: '',
      deslink: '',
      inactive: 0,
      technologies: []
    })
  }
});

const project = ref({ ...props.project });

watch(
  () => props.project, 
  (newProject) => {
    if (newProject.technologies && newProject.technologies.length) {
      project.value.technologies = newProject.technologies
        .map(tech => ({
          id: tech.idtechnology,
          name: tech.desname
        }));
    }
  }, 
  { immediate: true }
);

const toastRef = ref(null);

const isLoading = ref(false);

const buildFormData = (project) => {
  const formData = new FormData();
  if (project.idproject) formData.append('idproject', project.idproject);
  formData.append('destitle', project.destitle);
  formData.append('desdescription', project.desdescription);
  formData.append('deslink', project.deslink);
  formData.append('inactive', project.inactive);
  formData.append('technologies', project.technologies);
  if (project.desimage instanceof File) formData.append('image', project.desimage);
  return formData;
};

const emit = defineEmits(['onCloseModal']);

const save = async (project) => {
  isLoading.value = true;
  
  try {
    const formData = buildFormData(project);
    
    await axios.post('/projects/save', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    await nextTick();

    emit('onCloseModal');
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao adicionar/editar projeto');
  }
  
  isLoading.value = false;
};

const techs = ref([]);

const getTechs = async () => {
  try {
    const response = await axios.get('/technologies');
    
    techs.value = response.data ? response.data
      .map(tech => ({
        id: tech.idtechnology,
        name: tech.desname
      })) : [];
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao carregar tecnologias');
  }
};

onMounted(async () => {
  await getTechs();
});

const handleSelectChange = (newSelectedTechs) => {
  project.value.technologies = newSelectedTechs;
};

const submitForm = async (event) => {
  event.preventDefault();

  const formattedProject = {
    ...project.value,
    technologies: project.value.technologies.map(tech => tech.id).join(', ')
  };
  
  save(formattedProject);
};

const imagePreview = ref(undefined);

watch(
  () => project.value.desimage,
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

const rules = computed(() => ({
  destitle: { required },
  desdescription: { required },
  technologies: { required }
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

    <Input
      v-model="project.deslink"
      label="Link"
      placeholder="Link do projeto"
    />

    <MultiSelect
      label="Tecnologias"
      :options="techs"
      :selected="project.technologies"
      @handle-select-change="handleSelectChange"
    />

    <div class="flex flex-col gap-2">
      <label class="text-font font-semibold">Ativo</label>
      <Switch v-model="project.inactive" class="ml-1" />
    </div>

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
