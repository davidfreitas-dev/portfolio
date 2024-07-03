<script setup>
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, email } from '@vuelidate/validators';
import { jwtDecode } from 'jwt-decode';
import { useSessionStore } from '../../stores/session';
import axios from '../../api/axios';
import Wrapper from '../../components/Wrapper.vue';
import Button from '../../components/Button.vue';
import Checkbox from '../../components/Checkbox.vue';
import Input from '../../components/Input.vue';
import Toast from '../../components/Toast.vue';

const storeSession = useSessionStore();
const router = useRouter();
const isLoading = ref(false);
const remember = ref(false);
const formData = reactive({
  deslogin: '',
  despassword: ''
});

const signIn = async () => {
  isLoading.value = true;

  try {
    const response = await axios.post('/signin', formData);
    const userData = jwtDecode(response.data);
    const isAdmin = userData.inadmin === 1;
    
    if (isAdmin) {
      await storeSession.setSession({ token: response.data });
      router.push('/'); 
    } else {
      toastRef.value?.showToast('error', 'Acesso não autorizado: usuário não possui perfil de administrador');
    }
  } catch (error) {
    toastRef.value?.showToast(error.data.status, error.data.message);
  }

  isLoading.value = false;
};

const rules = computed(() => {
  return {
    deslogin: { required, email },
    despassword: { required }
  };
});

const v$ = useVuelidate(rules, formData);

const toastRef = ref(null);

const submitForm = async (event) => {
  event.preventDefault();

  const isFormCorrect = await v$.value.$validate();

  if (!isFormCorrect) {
    toastRef.value?.showToast('error', 'Informe um e-mail válido e a senha.');
    return;
  } 
  
  signIn();
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
          Faça login para usar nossa plataforma
        </span>
      </header>
      
      <form @submit="submitForm" class="flex flex-col p-5">
        <Input
          v-model="formData.deslogin"
          type="email"
          label="Endereço de e-mail"
          placeholder="johndoe@email.com"
        />

        <Input
          v-model="formData.despassword"
          type="password"
          label="Sua senha"
          placeholder="**********"
        />

        <Checkbox
          v-model="remember"
          :value="!remember"
          label="Lembre-se de mim"
          class="mb-7"
        />

        <Button
          class="my-5"
          :disabled="isLoading"
          :is-loading="isLoading"
        >
          Entrar na plataforma
        </Button>

        <router-link to="/forgot" class="text-primary hover:text-primary-hover outline-primary text-center cursor-pointer mt-3">
          Esqueci minha senha
        </router-link>
      </form>
    </Wrapper>

    <Toast ref="toastRef" />
  </div>
</template>