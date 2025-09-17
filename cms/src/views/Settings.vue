<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVuelidate } from '@vuelidate/core';
import { required, email, helpers } from '@vuelidate/validators';
import { useDark } from '@vueuse/core';
import { useAuthStore } from '@/stores/authStore';
import { useUserStore } from '@/stores/userStore';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Input from '@/components/Input.vue';
import Button from '@/components/Button.vue';
import Icon from '@/components/Icon.vue';
import Switch from '@/components/Switch.vue';
import Modal from '@/components/Modal.vue';
import Dialog from '@/components/Dialog.vue';

const router = useRouter();
const authStore = useAuthStore();
const userStore = useUserStore();

const formData = ref({
  name: '',
  email: ''
});

onMounted(() => {
  if (userStore.user) {
    formData.value.name = userStore.user.name;
    formData.value.email = userStore.user.email;
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

const needsReauth = computed(() => formData.value.email !== userStore.user?.email);

const modalRef = ref<InstanceType<typeof Modal> | null>(null);

const { showToast } = useToast();

const updateUserData = async () => {
  if (!userStore.user?.id) throw new Error('Usuário não encontrado');
  await userStore.updateUser(formData.value);
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
      if (!userStore.user?.email) {
        showToast('error', 'Usuário não encontrado.');
        return;
      }

      await authStore.signIn({
        login: userStore.user.email,
        password: currentPassword.value
      });

      await updateUserData();
      showToast('success', 'Informações atualizadas com sucesso.');
    } catch {
      showToast('error', 'Senha incorreta. Tente novamente.');
    }
  });
};

const dialogRef = ref<InstanceType<typeof Dialog> | null>(null);

const handleLogout = () => dialogRef.value?.openModal();

const handleConfirmLogout = () => {
  authStore.logOut();
  router.push({ name: 'Login' });
};

const isDark = useDark({
  selector: 'html',
  attribute: 'class',
  valueDark: 'dark',
  valueLight: '',
});
</script>

<template>
  <Container>
    <div class="header flex justify-between items-center">
      <Breadcrumb title="Configurações" description="Gerencie suas configurações aqui." />
      <Button class="h-fit" @click="handleLogout">
        <span class="hidden md:block">Encerrar Sessão</span>
        <Icon name="logout" class="md:ml-2" />
      </Button>
    </div>

    <section class="account my-7">
      <h1 class="section-title text-font dark:text-font-dark text-2xl font-semibold mb-3">
        Minha Conta
      </h1>
      <div class="p-7 border border-neutral dark:border-neutral-dark rounded-3xl">
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
            placeholder="joaodasilva@email.com"
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

    <section class="system my-7">
      <h1 class="section-title text-font dark:text-font-dark text-2xl font-semibold mb-3">
        Sistema
      </h1>
      <div class="p-7 border border-neutral dark:border-neutral-dark rounded-3xl">
        <div class="flex justify-between items-center">
          <span class="text-font dark:text-font-dark font-semibold">Modo escuro</span>
          <Switch v-model="isDark" />
        </div>
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

    <Dialog
      ref="dialogRef"
      header="Tem certeza que deseja sair?"
      message="Essa ação irá encerrar sua sessão atual."
      @confirm-action="handleConfirmLogout"
    />
  </Container>
</template>
