<script setup>
import { ref, reactive, computed } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, email } from '@vuelidate/validators';
import axios from '../../api/axios';
import Wrapper from '../../components/Wrapper.vue';
import Button from '../../components/Button.vue';
import Input from '../../components/Input.vue';
import Toast from '../../components/Toast.vue';

const toastRef = ref(null);
const isLoading = ref(false);
const formData = reactive({
  desemail: ''
});

const getPasswordResetLink = async () => {
  isLoading.value = true;

  try {
    const response = await axios.post('/forgot', formData);
    toastRef.value?.showToast(response.status, response.message);
  } catch (error) {
    toastRef.value?.showToast(error.data.status, error.data.message);
  }

  isLoading.value = false;
};

const rules = computed(() => {
  return {
    desemail: { required, email }
  };
});

const v$ = useVuelidate(rules, formData);

const submitForm = async (event) => {
  event.preventDefault();

  const isFormCorrect = await v$.value.$validate();
  
  if (!isFormCorrect) {
    toastRef.value?.showToast('error', 'Informe um e-mail válido');
    return;
  }

  getPasswordResetLink();
};
</script>

<template>
  <div class="flex items-center justify-center w-full h-screen text-gray-800 bg-gray-50">
    <Wrapper class="w-full max-w-md m-10">
      <header class="flex flex-col items-center">
        <h1 class="text-gray-800 font-bold font-sans text-2xl mt-5">
          Bem-vindo!
        </h1>
        <span class="font-sans text-sm text-secondary my-2">
          Recupere sua senha de acesso
        </span>
      </header>

      <form @submit="submitForm" class="flex flex-col p-5">
        <Input
          v-model="formData.desemail"
          type="email"
          label="Endereço de e-mail"
          placeholder="johndoe@email.com"
        />

        <Button :is-loading="isLoading" class="my-5">
          Enviar link de recuperação
        </Button>

        <router-link to="/signin" class="text-primary hover:text-primary-hover outline-primary text-center cursor-pointer mt-3">
          Voltar para o login
        </router-link>
      </form>

      <Toast ref="toastRef" />
    </wrapper>
  </div>
</template>