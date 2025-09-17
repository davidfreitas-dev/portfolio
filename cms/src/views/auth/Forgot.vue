<script setup lang="ts">
import { ref, computed } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, email } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';

const authStore = useAuthStore();

const { showToast } = useToast();

const formData = ref({
  email: ''
});

const rules = computed(() => ({
  email: { required, email }
}));

const v$ = useVuelidate(rules, formData);

const submitForm = async () => {
  const isValidForm = await v$.value.$validate();

  if (!isValidForm) {
    showToast('error', 'Informe um e-mail válido');
    return;
  }

  await authStore.forgotPassword(formData.value.email);

  showToast('success', 'Enviamos um link de recuperação para o seu e-mail');
};

const logoPath = new URL('@/assets/logo.png', import.meta.url).href;
</script>

<template>
  <div class="flex md:items-center justify-center w-full h-screen">
    <div class="md:bg-background dark:md:bg-accent-dark md:shadow-lg md:rounded-xl md:px-8 px-4 py-4 w-full max-w-lg">
      <div class="flex flex-col items-center my-3">
        <div class="flex items-center">
          <img
            :src="logoPath"
            alt="Logo Time Freela"
            class="h-12"
          >
          <h3 class="text-font dark:text-font-dark text-4xl font-extrabold">
            Time<span class="text-primary ml-0.5">Freela</span>
          </h3>
        </div>
        <span class="font-sans text-sm text-secondary mt-1">
          Informe seu e-mail para recuperar sua senha
        </span>
      </div>

      <form class="flex flex-col gap-5 p-3" @submit.prevent="submitForm">
        <Input
          v-model="formData.email"
          type="email"
          label="Endereço de e-mail"
          placeholder="joaodasilva@email.com"
          :error="v$.email.$dirty && v$.email.$error ? 'Informe um endereço de e-mail válido' : ''"
          @blur="v$.email.$touch"
        />

        <Button :disabled="authStore.isLoading" :is-loading="authStore.isLoading">
          Recuperar senha
        </Button>

        <router-link
          to="/login"
          class="text-center text-sm text-primary dark:text-primary-dark hover:text-primary-hover dark:hover:text-primary-hover-dark cursor-pointer mt-3"
        >
          Voltar ao login
        </router-link>
      </form>
    </div>
  </div>
</template>
