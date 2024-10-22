<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { jwtDecode } from 'jwt-decode';
import { useVuelidate } from '@vuelidate/core';
import { required, email, minLength } from '@vuelidate/validators';
import { useSessionStore } from '../stores/session';
import axios from '../api/axios';
import MainContainer from '../components/shared/MainContainer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import Wrapper from '../components/shared/Wrapper.vue';
import Input from '../components/shared/Input.vue';
import Button from '../components/shared/Button.vue';
import Toast from '../components/shared/Toast.vue';
import Dialog from '../components/shared/Dialog.vue';

const router = useRouter();
const storeSession = useSessionStore();
const isLoading = ref(false);
const toastRef = ref(null);
const modalRef = ref(null);
const userData = ref({});

const loadData = async () => {
  const token = storeSession.session ? storeSession.session.token : '';

  if (token) {
    userData.value = jwtDecode(token);
  } else {
    toastRef.value?.showToast('error', 'Falha ao carregar dados do usuário.');
  }
};

onMounted(async () => {
  await loadData();
});

const handleLogout = () => {
  modalRef.value?.openModal();
};

const logout = () => {
  storeSession.clearSession();
  router.push('/signin'); 
};

const rules = computed(() => {
  return {
    deslogin: { required },
    desperson: { required },
    desemail: { required, email },
    nrphone: { minLength: minLength(11) }
  };
});

const v$ = useVuelidate(rules, userData);

const isFormValid = computed(() => {
  return v$.value.$pending || v$.value.$invalid;
});

const save = async () => {
  const userId = userData.value.iduser;

  try {
    const response = await axios.put(`/users/update/${userId}`, userData.value);
    await storeSession.setSession({ token: response.data });
    toastRef.value?.showToast(response.status, response.message);
  } catch (error) {
    toastRef.value?.showToast(error.data.status, error.data.message);
  }
};

const submitForm = async (event) => {
  event.preventDefault();

  isLoading.value = true;

  await save();

  isLoading.value = false;
};
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Configurações" description="Atualize suas informações pessoais aqui.">
      <Button @click="handleLogout">
        <span class="material-icons">
          logout
        </span>
        
        <span class="hidden md:block">
          Sair
        </span>
      </Button>
    </Breadcrumb>
    
    <Wrapper>
      <form class="form flex flex-col px-4 my-10" @submit="submitForm">
        <Input
          v-model="userData.deslogin"
          label="Nome de usuário"
          placeholder="johndoe"
        />

        <Input
          v-model="userData.desperson"
          label="Nome e sobrenome"
          placeholder="John Doe"
        />

        <Input
          v-model="userData.desemail"
          label="E-mail"
          placeholder="johndoe@email.com"
        />

        <div class="flex flex-row-reverse mt-4">
          <Button :is-loading="isLoading" :disabled="isLoading || isFormValid">
            Salvar Alterações
          </Button>
        </div>
      </form>
    </Wrapper>

    <Dialog
      ref="modalRef"
      header="Deseja realmente sair?"
      message="Todas as sessões ativas serão encerradas."
      @confirm-action="logout"
    />

    <Toast ref="toastRef" />
  </MainContainer>
</template>