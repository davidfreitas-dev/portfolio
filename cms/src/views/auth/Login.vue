<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, email, minLength } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';

const router = useRouter();
const authStore = useAuthStore();

const formData = ref({
  email: '',
  password: ''
});

const rules = computed(() => ({
  email: { required, email },
  password: { required, minLength: minLength(8) }
}));

const v$ = useVuelidate(rules, formData);

const { showToast } = useToast();

const submitForm = async () => {
  const isValidForm = await v$.value.$validate();

  if (!isValidForm) {
    showToast('error', 'Preencha os campos corretamente');
    return;
  }

  try {
    await authStore.login({
      email: formData.value.email,
      password: formData.value.password
    });
  } catch (error: any) {
    showToast('error', error?.response?.data?.message || error.message || 'Falha na autenticação');
    return;
  }

  if (!authStore.isAuthenticated) {
    showToast('error', 'Falha na autenticação');
    return;
  }

  router.push({ name: 'Home' });
};
</script>

<template>
  <div class="flex md:items-center justify-center w-full h-screen">
    <div class="md:bg-background dark:md:bg-accent-dark md:shadow-lg md:rounded-xl md:px-8 px-4 py-4 w-full max-w-lg">
      <div class="flex flex-col items-center my-3">
        <div class="flex items-center">
          <h3 class="text-font dark:text-font-dark text-4xl font-extrabold">
            Dave<span class="text-primary ml-0.5">Dev</span>
          </h3>
        </div>
        <span class="font-sans text-sm text-secondary mt-1">
          Faça login para usar nossa plataforma
        </span>
      </div>

      <form class="flex flex-col gap-5 p-3" @submit.prevent="submitForm">
        <Input
          v-model="formData.email"
          type="email"
          label="Endereço de e-mail"
          placeholder="exemplo@email.com"
          :error="v$.email.$dirty && v$.email.$error ? 'Informe um endereço de e-mail válido' : ''"
          @blur="v$.email.$touch"
        />

        <Input
          v-model="formData.password"
          type="password"
          label="Sua senha"
          placeholder="**********"
          :error="v$.password.$dirty && v$.password.$error ? 'A senha deve ter no mínimo 8 caracteres' : ''"
          @blur="v$.password.$touch"
        />

        <router-link
          to="/forgot"
          class="text-right text-sm text-primary dark:text-primary-dark hover:text-primary-hover dark:hover:text-primary-hover-dark outline-primary dark:outline-primary-dark cursor-pointer"
        >
          Esqueci minha senha
        </router-link>

        <Button :disabled="authStore.isLoading" :is-loading="authStore.isLoading">
          Entrar na plataforma
        </Button>

      </form>
    </div>
  </div>
</template>
