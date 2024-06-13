<script setup>
const props = defineProps({
  title: { 
    type: String, 
    default: '' 
  },
  description: { 
    type: String, 
    default: '' 
  }
});
</script>

<template>
  <div>
    <h1 class="text-3xl font-semibold mb-1">
      {{ title }}
    </h1>
    <p class="text-secondary pr-4">
      {{ description }}
    </p>
  </div>
</template>