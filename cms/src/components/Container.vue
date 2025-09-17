<script setup lang="ts">
const { sidebarWidth } = defineProps<{
  sidebarWidth?: string
}>();
</script>

<template>
  <main class="h-screen overflow-y-auto transition-all ease-in-out duration-200 p-6" :style="{ width: `calc(100% - ${sidebarWidth})` }">
    <slot />
  </main>
</template>
