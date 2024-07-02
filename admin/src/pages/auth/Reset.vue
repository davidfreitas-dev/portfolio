<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router'; 
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, helpers } from '@vuelidate/validators';
import axios from '../../api/axios';
import Wrapper from '../../components/Wrapper.vue';
import Button from '../../components/Button.vue';
import Input from '../../components/Input.vue';
import Toast from '../../components/Toast.vue';

const route = useRoute();
const router = useRouter();
const toastRef = ref(undefined);
const formData = reactive({
  iduser: '',
  idrecovery: '',
  despassword: ''
});

const decryptToken = async (code) => {
  try {
    const response = await axios.post('/forgot/token', { code: code });
    formData.iduser = response.data.iduser;
    formData.idrecovery = response.data.idrecovery;
  } catch (error) {
    toastRef.value?.showToast(error.data.status, error.data.message);
  }
};

onMounted(async () => {
  const code = route.query.code;

  if (!code) {
    router.push('/signin');
    return;
  }

  await decryptToken(code);
});

const isLoading = ref(false);

const resetPassword = async () => {
  isLoading.value = true;

  try {
    const response = await axios.post('/forgot/reset', formData);
    toastRef.value?.showToast(response.status, response.data);
    formData.despassword = '';
  } catch (error) {
    toastRef.value?.showToast(error.data.status, error.data.message);
  }

  isLoading.value = false;
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
    toastRef.value?.showToast('error', 'Sua senha deve conter pelo menos 6 caracteres, um número, letra maiúscula e minúscula');
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

        <Button
          class="my-5"
          :disabled="isLoading"
          :is-loading="isLoading"
        >
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