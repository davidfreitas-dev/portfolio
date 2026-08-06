<script setup lang="ts">
import { reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, maxLength, numeric } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import { useLoading } from '@/composables/useLoading';
import type { ApiResponse } from '@/types';
import axios from 'axios';
import InputOtp from '@/components/InputOtp.vue';
import Button from '@/components/Button.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { showToast } = useToast();
const { isLoading, withLoading } = useLoading();

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

  await withLoading(async () => {
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
    } catch (error: unknown) {
      let message = 'Falha ao validar o código.';
 
      if (axios.isAxiosError<ApiResponse>(error)) {
        message = error.response?.data?.message || message;
      } else if (error instanceof Error) {
        message = error.message;
      }
 
      showToast('error', message);
    }
  });
};
</script>

<template>
  <div class="flex items-center justify-center w-full min-h-screen md:bg-gray-100 dark:md:bg-gray-900 p-4 md:p-8">
    <div class="w-full max-w-[1000px] flex flex-col md:flex-row bg-white dark:bg-gray-600 md:shadow-xl rounded-2xl overflow-hidden relative">
      <!-- Left: Branding & Welcome -->
      <div class="w-full md:w-5/12 bg-primary-default relative p-8 md:p-12 flex flex-col justify-between text-white overflow-hidden">
        <!-- Decorative background elements -->
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none opacity-40 text-primary-focus ">
          <svg
            class="absolute w-[150%] h-[150%] -top-1/4 -left-1/4 animate-[spin_60s_linear_infinite]"
            preserveAspectRatio="none"
            viewBox="0 0 100 100"
          >
            <path
              d="M0,50 a1,1 0 0,0 100,0 a1,1 0 0,0 -100,0"
              fill="currentColor"
              opacity="0.3"
              transform="scale(0.8) translate(10, 10)"
            />
            <path
              d="M0,50 a1,1 0 0,0 100,0 a1,1 0 0,0 -100,0"
              fill="currentColor"
              opacity="0.1"
              transform="scale(1.2) translate(-10, -10)"
            />
          </svg>
        </div>
        <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-primary-focus rounded-full blur-3xl opacity-50 mix-blend-screen" />
 
        <div class="relative z-10 flex items-center mb-8 md:mb-12">
          <h3 class="text-3xl font-extrabold tracking-tight">
            Dave<span class="text-white/80 ml-0.5">Dev</span>
          </h3>
        </div>
 
        <div class="relative z-10 space-y-4 md:space-y-6">
          <h1 class="text-3xl md:text-4xl font-extrabold leading-tight">
            Verificação de Segurança
          </h1>
          <p class="text-base md:text-lg text-white/90">
            Estamos quase lá. Por favor, insira o código que enviamos para o seu e-mail para validar sua solicitação.
          </p>
        </div>
 
        <div class="relative z-10 mt-12 md:mt-24 pt-6 md:pt-8 border-t border-white/20 flex items-start gap-3">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-6 h-6 text-white/80 shrink-0"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"
            />
          </svg>
          <p class="text-sm text-white/80">
            Mantenha suas credenciais seguras e não compartilhe sua senha.
          </p>
        </div>
      </div>
 
      <!-- Right: Form -->
      <div class="w-full md:w-7/12 p-8 md:p-12 lg:p-16 flex flex-col justify-center">
        <div class="mb-8 md:mb-10 text-center md:text-left">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-700 dark:text-gray-100 mb-2">
            Validar Código
          </h2>
          <p class="text-gray-500 dark:text-gray-300 text-sm md:text-base">
            Insira o código de 6 dígitos que enviamos para <strong class="text-gray-700 dark:text-gray-100">{{ formData.email }}</strong>
          </p>
        </div>
 
        <form class="flex flex-col gap-5 w-full max-w-md mx-auto md:mx-0" @submit.prevent="handleValidateCode">
          <InputOtp
            v-model="formData.code"
            label="Código de 6 dígitos"
            :length="6"
            :error="v$.code.$dirty && v$.code.$error ? 'Informe o código de 6 dígitos' : ''"
            :disabled="isLoading"
          />

          <div class="mt-2">
            <Button
              class="w-full"
              :is-loading="isLoading"
            >
              Validar Código
            </Button>
          </div>

          <div class="flex justify-center mt-2">
            <router-link :to="{ name: 'Forgot' }" class="text-sm text-primary-default hover:text-primary-hover outline-primary cursor-pointer transition-colors">
              Reenviar código
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
