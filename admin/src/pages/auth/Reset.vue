<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router'; 
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, helpers } from '@vuelidate/validators';
import axios from '../../api/axios';
import Wrapper from '../../components/Wrapper.vue';
import Button from '../../components/Button.vue';
import Input from '../../components/Input.vue';
import Toast from '../../components/Toast.vue';

const route = useRoute();
const code = ref(route.query.code);
const validToken = ref(false);
const toastRef = ref(null);

const decryptToken = async () => {
  const response = await axios.post('/forgot/token', { code: code.value });
    
  if (response.status === 'error') {
    validToken.value = false;
    toastRef.value?.showToast(response.status, response.data);
    return;
  }

  validToken.value = true;
  formData.iduser = response.data.iduser;
  formData.idrecovery = response.data.idrecovery;
};

onMounted(() => {
  decryptToken();
});

const formData = reactive({
  iduser: '',
  idrecovery: '',
  despassword: ''
});

const isLoading = ref(false);

const resetPassword = async () => {
  isLoading.value = true;

  const response = await axios.post('/forgot/reset', formData);

  isLoading.value = false;

  if (response.status === 'error') {
    toastRef.value?.showToast(response.status, response.data);
    return;
  }

  toastRef.value?.showToast(response.status, response.data);

  formData.despassword = '';
};

const customPasswordValidator = helpers.regex('passwordValidator', /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/);

const rules = computed(() => {
  return {
    despassword: { 
      required, 
      minLength: minLength(6),
      customPasswordValidator
    }
  };
});

const v$ = useVuelidate(rules, formData);

const submitForm = async (event) => {
  event.preventDefault();

  const isFormCorrect = await v$.value.$validate();

  if (!isFormCorrect) {
    toastRef.value?.showToast('error', 'Sua senha deve conter pelo menos 6 caracteres, um numero, letra maiúscula e minúscula');
    return;
  } 

  if (!validToken.value) {
    toastRef.value?.showToast('error', 'Token de redefinição de senha inválido');
    return;
  }

  resetPassword();
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
          Redefina sua senha de acesso
        </span>
      </header>

      <form @submit="submitForm" class="flex flex-col p-5">
        <Input
          v-model="formData.despassword"
          type="password"
          label="Nova senha"
          placeholder="**********"
        />

        <Button :is-loading="isLoading" class="my-5">
          Redefinir senha
        </Button>

        <router-link to="/signin" class="text-primary hover:text-primary-hover outline-primary text-center cursor-pointer mt-3">
          Voltar para o login
        </router-link>
      </form>

      <Toast ref="toastRef" />
    </wrapper>
  </div>
</template>