<script setup>
import BaseLayout from '../components/BaseLayout.vue';
import Button from '../components/Button.vue';
</script>

<template>
  <BaseLayout>
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-semibold">
        Experiences
      </h1>
      <Button
        :is-loading="isLoading"
        text="Nova Experiência"
      />
    </div>
    <p class="text-secondary">
      This is the experiences page
    </p>
  </BaseLayout>
</template>
