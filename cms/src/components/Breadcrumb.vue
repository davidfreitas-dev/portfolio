<script setup lang="ts">
const { title, description } = defineProps<{
 title: string
 description?: string
}>();
</script>

<template>
  <div class="breadcrumb-wrapper flex justify-between items-start">
    <div class="breadcrumb">
      <h1 class="text-gray-700 dark:text-gray-100 text-3xl font-bold mb-1 leading-6">
        {{ title }}
      </h1>
      <p class="text-gray-400 dark:text-gray-400 pt-1">
        {{ description }}
      </p>
    </div>
  </div>
</template>
