<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { jwtDecode } from 'jwt-decode';
import { useSessionStore } from '../stores/session';
import MainContainer from '../components/MainContainer.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Wrapper from '../components/Wrapper.vue';
import Input from '../components/Input.vue';
import Button from '../components/Button.vue';
import Toast from '../components/Toast.vue';

const router = useRouter();
const storeSession = useSessionStore();
const toastRef = ref(null);
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

const logout = () => {
  storeSession.clearSession();
  router.push('/signin'); 
};
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Configurações" description="Atualize suas informações pessoais aqui.">
      <Button @click="logout">
        <span class="material-icons">
          logout
        </span>
        
        <span class="hidden md:block">
          Sair
        </span>
      </Button>
    </Breadcrumb>
    
    <Wrapper>
      <form class="form flex flex-col px-4 my-10">
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

        <Input
          v-model="userData.nrphone"
          label="Telefone"
          placeholder="(11)99999-9999"
        />

        <div class="flex flex-row-reverse mt-4">
          <Button>
            Salvar Alterações
          </Button>
        </div>
      </form>
    </Wrapper>

    <Toast ref="toastRef" />
  </MainContainer>
</template>