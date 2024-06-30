<script setup>
import MainContainer from '../components/MainContainer.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Wrapper from '../components/Wrapper.vue';
import Input from '../components/Input.vue';
import Button from '../components/Button.vue';
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Configurações" description="Atualize suas informações pessoais aqui.">
      <Button>
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
        <Input label="Nome de usuário" placeholder="johndoe" />

        <Input label="Nome e sobrenome" placeholder="John Doe" />

        <Input label="E-mail" placeholder="johndoe@email.com" />

        <Input label="Telefone" placeholder="(11)99999-9999" />

        <div class="flex flex-row-reverse mt-4">
          <Button>
            Salvar Alterações
          </Button>
        </div>
      </form>
    </Wrapper>
  </MainContainer>
</template>