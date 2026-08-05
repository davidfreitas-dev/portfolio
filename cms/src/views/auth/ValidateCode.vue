<script setup lang="ts">
import { reactive, onMounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, maxLength, numeric } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import axios from 'axios';
import InputOtp from '@/components/InputOtp.vue';
import Button from '@/components/Button.vue';
import Logo from '@/components/Logo.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { showToast } = useToast();

const isLoading = ref(false);

const formData = reactive({
  email: (route.query.email as string) || '',
  code: '',
});

const rules = { 
  email: { required },
  code: { 
    required, 
    numeric,
    minLength: minLength(6), 
    maxLength: maxLength(6) 
  },
};

const v$ = useVuelidate(rules, formData);

onMounted(() => {
  if (!formData.email) {
    showToast('error', 'E-mail não informado');
    router.push({ name: 'Forgot' });
  }
});

const delay = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

const handleValidateCode = async () => {
  const isValidForm = await v$.value.$validate();
  
  if (!isValidForm) {
    showToast('error', 'Preencha o código corretamente (6 dígitos)');
    return;
  }

  isLoading.value = true;
  try {      
    await authStore.validateResetCode(formData); 
    
    showToast('success', 'Código validado com sucesso!');       
    
    await delay(1500);

    router.push({ 
      name: 'ResetPassword',
      query: { 
        email: formData.email,
        code: formData.code
      }
    }); 
  } catch (error: any) {
    let message = 'Falha ao validar o código.';
    
    if (axios.isAxiosError(error)) {
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
    <div class="md:bg-background dark:md:bg-accent-dark md:shadow-lg md:rounded-xl md:px-8 px-4 py-4 w-full h-screen md:h-auto md:max-w-lg">
      <div class="flex flex-col items-center my-5">
        <Logo :is-expanded="true" class="scale-125 mb-4" />
      </div>

      <form class="flex flex-col gap-4 p-3" @submit.prevent="handleValidateCode">
        <p class="text-sm text-secondary dark:text-secondary-dark mb-4 text-center">
          Insira o código de 6 dígitos que enviamos para o seu e-mail: <br>
          <strong class="text-font dark:text-font-dark">{{ formData.email }}</strong>
        </p>

        <InputOtp
          v-model="formData.code"
          label="Código de 6 dígitos"
          :length="6"
          :error="v$.code.$dirty && v$.code.$error ? 'Informe o código de 6 dígitos' : ''"
          :disabled="isLoading"
        />

        <Button :disabled="isLoading" :is-loading="isLoading" class="mt-6">
          Validar código
        </Button>

        <router-link :to="{ name: 'Forgot' }" class="text-center text-sm text-primary dark:text-primary-dark hover:text-primary-hover dark:hover:text-primary-hover-dark cursor-pointer m-4">
          Reenviar código
        </router-link>
      </form>
    </div>
  </div>
</template>
