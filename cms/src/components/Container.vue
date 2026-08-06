<script setup lang="ts">
// sidebarWidth não é mais necessário no cálculo de largura pois o App.vue usa padding (pl-...)
defineProps<{
 sidebarWidth?: string
}>();
</script>

<template>
  <div class="w-full transition-all ease-in-out duration-200 p-6 lg:p-8">
    <slot />
  </div>
</template>
