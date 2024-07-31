<script setup>
import { ref, computed, watch } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required } from '@vuelidate/validators';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import Toast from '../shared/Toast.vue';

const props = defineProps({
  technology: {
    type: Object,
    default: () => ({
      desname: ''
    })
  }
});

const emit = defineEmits(['onCloseModal']);

const technology = ref({ ...props.technology });

watch(() => props.technology, (newValue) => {
  technology.value = { ...newValue };
}, { deep: true });

const rules = computed(() => ({
  desname: { required }
}));

const v$ = useVuelidate(rules, technology);

const isFormValid = computed(() => v$.value.$pending || v$.value.$invalid);

const toastRef = ref(null);

const isLoading = ref(false);

const save = async (technology) => {
  isLoading.value = true;

  try {
    const response = !technology.idtechnology 
      ? await axios.post('/technologies/create', technology)
      : await axios.put(`/technologies/update/${technology.idtechnology}`, technology) ;

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
  save(technology.value);
};

const deleteTechnology = async (technologyId) => {
  isLoading.value = true;

  try {
    const response = await axios.delete(`/technologies/delete/${technologyId}`);
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
        color="outline"
        class="mt-5 mr-3"
        :is-loading="isLoading"
        :disabled="isLoading"
        @click="deleteTechnology(technology.idtechnology)"
      >
        Excluir Dados
      </Button>
    </div>
  </form>

  <Toast ref="toastRef" />
</template>
