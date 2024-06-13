<script setup>
const props = defineProps({
  sidebarWidth: {
    type: String,
    default: '250px'
  }
});
</script>

<template>
  <main :class="`bg-accent transition-all ease-in-out duration-200 p-8 w-[calc(100%-${sidebarWidth})]`">
    <slot />
  </main>
</template>