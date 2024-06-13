<script setup>
import MainContainer from '../components/MainContainer.vue';
import Button from '../components/Button.vue';
</script>

<template>
  <MainContainer>
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-semibold">
        Projects
      </h1>
      <Button
        :is-loading="isLoading"
        text="Novo Projeto"
      />
    </div>
    <p class="text-secondary">
      This is the projects page
    </p>
  </MainContainer>
</template>
