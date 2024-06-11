<script setup>
import BaseLayout from '../components/BaseLayout.vue';
import Input from '../components/Input.vue';
import Button from '../components/Button.vue';
</script>

<template>
  <BaseLayout>
    <h1 class="text-3xl font-semibold">
      Home
    </h1>
    <p>This is the home page</p>
    <form class="my-4">
      <Input
        type="text"
        label="Label"
        placeholder="Placeholder"
      />

      <Input
        type="text"
        label="Label"
        placeholder="Placeholder"
      />

      <Input
        type="text"
        label="Label"
        placeholder="Placeholder"
      />

      <Button
        :is-loading="isLoading"
        text="Entrar na plataforma"
        class="mt-4"
      />
    </form>
  </BaseLayout>
</template>
