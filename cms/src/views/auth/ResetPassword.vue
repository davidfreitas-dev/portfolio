<script setup lang="ts">
import { reactive, onMounted, computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, sameAs } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import type { ApiResponse } from '@/types';
import axios from 'axios';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';
import Logo from '@/components/Logo.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { showToast } = useToast();
const isLoading = ref(false);

const formData = reactive({
  email: (route.query.email as string) || '',
  code: (route.query.code as string) || '',
  password: '',
  password_confirm: '',
});

const rules = { 
  email: { required },
  code: { required },
  password: { required, minLength: minLength(8) },
  password_confirm: { 
    required, 
    sameAsPassword: sameAs(computed(() => formData.password)) 
  },
};

const v$ = useVuelidate(rules, formData);

onMounted(() => {
  if (!formData.email || !formData.code) {
    showToast('error', 'Dados de redefinição incompletos');
    router.push('/forgot');
  }
});

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

const handleResetPassword = async () => {
  const isValidForm = await v$.value.$validate();

  if (!isValidForm) {
    showToast('error', 'Verifique os campos da senha');
    return;
  }

  isLoading.value = true;
  try {      
    await authStore.resetPassword({
      email: formData.email,
      code: formData.code,
      password: formData.password,
      password_confirm: formData.password_confirm
    }); 

    showToast('success', 'Senha redefinida com sucesso!');       

    await delay(1500);

    router.push('/login'); 
  } catch (error: unknown) {
    let message = 'Falha ao redefinir a senha.';

    if (axios.isAxiosError<ApiResponse>(error)) {
      message = error.response?.data?.message || message;
    }

    showToast('error', message);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="flex md:items-center justify-center w-full h-screen">
    <div class="bg-background dark:bg-accent-dark md:shadow-lg md:rounded-xl md:px-8 px-4 py-4 w-full h-screen md:h-auto md:max-w-lg">
      <div class="flex flex-col items-center my-5">
        <Logo :is-expanded="true" class="scale-125 mb-4" />
      </div>

      <form class="flex flex-col gap-4 p-3" @submit.prevent="handleResetPassword">
        <p class="text-sm text-secondary dark:text-secondary-dark mb-2 text-center">
          Crie uma nova senha segura para a sua conta.
        </p>

        <Input
          v-model="formData.password"
          type="password"
          label="Nova Senha"
          placeholder="••••••••"
          :error="v$.password.$dirty && v$.password.$error ? 'A senha deve ter pelo menos 8 caracteres' : ''"
          :disabled="isLoading"
          @blur="v$.password.$touch"
        />

        <Input
          v-model="formData.password_confirm"
          type="password"
          label="Confirmar Nova Senha"
          placeholder="••••••••"
          :error="v$.password_confirm.$dirty && v$.password_confirm.$error ? 'As senhas não coincidem' : ''"
          :disabled="isLoading"
          @blur="v$.password_confirm.$touch"
        />

        <Button :is-loading="isLoading" class="mt-5">
          Redefinir Senha
        </Button>

        <router-link to="/login" class="text-center text-sm text-primary-hover dark:text-primary-hover-dark hover:text-primary dark:hover:text-primary-dark outline-primary dark:outline-primary-dark cursor-pointer m-4">
          Cancelar e voltar ao login
        </router-link>
      </form>
    </div>
  </div>
</template>
