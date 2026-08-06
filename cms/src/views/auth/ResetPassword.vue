<script setup lang="ts">
import { reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, sameAs } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import { useLoading } from '@/composables/useLoading';
import type { ApiResponse } from '@/types';
import axios from 'axios';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { showToast } = useToast();
const { isLoading, withLoading } = useLoading();

const formData = reactive({
  email: (route.query.email as string) || '',
  code: (route.query.code as string) || '',
  password: '',
  password_confirm: '',
});

const isMinLength = computed(() => formData.password.length >= 8);
const hasUpperCase = computed(() => /[A-Z]/.test(formData.password));
const hasNumberOrSpecial = computed(() => /[0-9]/.test(formData.password) || /[^a-zA-Z0-9\s]/.test(formData.password));

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

  await withLoading(async () => {
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
    }
  });
};
</script>

<template>
  <div class="flex items-center justify-center w-full min-h-screen md:bg-gray-100 dark:md:bg-gray-700 p-4 md:p-8">
    <div class="w-full max-w-[1000px] flex flex-col md:flex-row bg-white dark:bg-gray-600 md:shadow-xl rounded-2xl overflow-hidden relative">
      <!-- Left: Branding & Welcome -->
      <div class="w-full md:w-5/12 bg-[var(--color-primary-default)] relative p-8 md:p-12 flex flex-col justify-between text-white overflow-hidden">
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
            Nova Senha.
          </h1>
          <p class="text-base md:text-lg text-white/90">
            Crie uma nova senha forte e única para proteger sua conta.
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
            Redefinir senha
          </h2>
          <p class="text-gray-500 dark:text-gray-300 text-sm md:text-base">
            Crie uma nova senha segura para a sua conta.
          </p>
        </div>
 
        <form class="flex flex-col gap-5 w-full max-w-md mx-auto md:mx-0" @submit.prevent="handleResetPassword">
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

          <!-- Password Requirements -->
          <div class="bg-primary-light/10 border border-primary-light/20 rounded-xl p-4 md:p-5">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-100 mb-3">
              Requisitos da senha:
            </h4>
            <ul class="space-y-2">
              <li class="flex items-center gap-2 text-sm" :class="isMinLength ? 'text-success dark:text-success-dark' : 'text-gray-500 dark:text-gray-300'">
                <svg
                  v-if="isMinLength"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                Mínimo 8 caracteres
              </li>
              <li class="flex items-center gap-2 text-sm" :class="hasUpperCase ? 'text-success dark:text-success-dark' : 'text-gray-500 dark:text-gray-300'">
                <svg
                  v-if="hasUpperCase"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                Uma letra maiúscula
              </li>
              <li class="flex items-center gap-2 text-sm" :class="hasNumberOrSpecial ? 'text-success dark:text-success-dark' : 'text-gray-500 dark:text-gray-300'">
                <svg
                  v-if="hasNumberOrSpecial"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                Um número ou caractere especial
              </li>
            </ul>
          </div>

          <div class="mt-2">
            <Button
              class="w-full"
              :is-loading="isLoading"
            >
              Redefinir Senha
            </Button>
          </div>

          <div class="flex justify-center mt-2">
            <router-link to="/login" class="text-sm text-[var(--color-primary-default)] hover:text-primary-hover outline-primary cursor-pointer transition-colors">
              Cancelar e voltar ao login
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
