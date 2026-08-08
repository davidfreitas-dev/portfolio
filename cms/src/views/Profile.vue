<script setup lang="ts">
import { computed, ref, watchEffect } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { required, email, helpers } from '@vuelidate/validators';
import { useAuthStore } from '@/stores/authStore';
import { useProfileStore } from '@/stores/profileStore';
import { storeToRefs } from 'pinia';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';
import Modal from '@/components/Modal.vue';

const authStore = useAuthStore();
const profileStore = useProfileStore();

const formData = ref({
  name: '',
  email: ''
});

const { user } = storeToRefs(profileStore);

watchEffect(() => {
  if (user.value) {
    formData.value.name = user.value.name || '';
    formData.value.email = user.value.email || '';
  }
});

const fullName = helpers.withMessage(
  'Digite nome e sobrenome',
  (value: string) => {
    if (typeof value !== 'string') return false;
    return value.trim().split(/\s+/).length >= 2;
  }
);

const rules = computed(() => ({
  name: { required, fullName },
  email: { required, email }
}));

const v$ = useVuelidate(rules, formData);

const needsReauth = computed(() => formData.value.email !== profileStore.user?.email);

const modalRef = ref<InstanceType<typeof Modal> | null>(null);

const { showToast } = useToast();

const updateUserData = async () => {
  if (!profileStore.user?.id) throw new Error('Usuário não encontrado');
  await profileStore.updateProfile(formData.value);
  modalRef.value?.closeModal();
};

const { isLoading: isSaving, withLoading: withSaving } = useLoading();

const handleSave = async (event: Event) => {
  event.preventDefault();

  v$.value.$touch();

  if (v$.value.$invalid) {
    showToast('error', 'Preencha os campos corretamente');
    return;
  }

  if (needsReauth.value) {
    modalRef.value?.openModal();
    return;
  }

  await withSaving(async () => {
    await updateUserData();
    showToast('success', 'Informações atualizadas com sucesso.');
  });
};

const currentPassword = ref('');

const passwordRules = computed(() => ({
  currentPassword: { required }
}));

const vPassword$ = useVuelidate(passwordRules, { currentPassword });

const { isLoading: isConfirming, withLoading: withConfirming } = useLoading();

const confirmPassword = async (event: Event) => {
  event.preventDefault();

  vPassword$.value.$touch();
  if (vPassword$.value.$invalid) return;

  await withConfirming(async () => {
    try {
      if (!profileStore.user?.email) {
        showToast('error', 'Usuário não encontrado.');
        return;
      }

      await authStore.login({
        email: profileStore.user.email,
        password: currentPassword.value
      });

      await updateUserData();
      showToast('success', 'Informações atualizadas com sucesso.');
    } catch {
      showToast('error', 'Senha incorreta. Tente novamente.');
    }
  });
};

</script>

<template>
  <Container>
    <div class="header flex justify-between items-center">
      <Breadcrumb title="Perfil" description="Gerencie suas informações de perfil aqui." />
    </div>

    <section class="account my-7">
      <div class="p-7 bg-white dark:bg-gray-800 shadow-md rounded-3xl">
        <h2 class="text-gray-700 dark:text-gray-100 text-xl font-semibold mb-5">
          Minha Conta
        </h2>
        <form class="flex flex-col gap-5">
          <Input
            v-model="formData.name"
            type="text"
            label="Nome e sobrenome"
            placeholder="João da Silva"
            :error="v$.name.$dirty && v$.name.$error ? 'O nome e sobrenome são obrigatórios' : ''"
            @blur="v$.name.$touch"
          />

          <Input
            v-model="formData.email"
            type="email"
            label="Endereço de e-mail"
            placeholder="exemplo@email.com"
            :error="v$.email.$dirty && v$.email.$error ? 'Informe um endereço de e-mail válido' : ''"
            @blur="v$.email.$touch"
          />

          <div class="flex justify-end">
            <Button
              class="w-fit"
              :is-loading="isSaving"
              @click="handleSave"
            >
              Salvar Alterações
            </Button>
          </div>
        </form>
      </div>
    </section>



    <Modal
      ref="modalRef"
      title="Confirme sua senha"
      align="center"
    >
      <form class="flex flex-col gap-5">
        <Input
          v-model="currentPassword"
          type="password"
          label="Senha atual"
          placeholder="Digite sua senha"
          :error="vPassword$.currentPassword.$dirty && vPassword$.currentPassword.$error ? 'Senha obrigatória' : ''"
          @blur="vPassword$.currentPassword.$touch"
        />

        <div class="flex justify-end">
          <Button
            class="w-fit"
            :is-loading="isConfirming"
            @click="confirmPassword"
          >
            Confirmar
          </Button>
        </div>
      </form>
    </Modal>
  </Container>
</template>
