<script setup>
import Loader from './Loader.vue';

const props = defineProps({
  isLoading: Boolean,
  size: { 
    type: String, 
    default: '' 
  },
  color: { 
    type: String, 
    default: 'primary' 
  },
});
</script>

<template>
  <button type="submit" :class="{ 'primary': color === 'primary', 'secondary': color === 'secondary', 'outline': color === 'outline', 'w-full text-center': size === 'full' }">
    <Loader v-if="isLoading" />
    <slot v-else />
  </button>
</template>

<style scoped>
.primary {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-primary font-semibold text-white text-base rounded-md transition-colors hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-green-600 focus:bg-primary active:bg-primary-pressed disabled:bg-gray-300
}
.secondary {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-white font-semibold text-gray-700 text-base rounded-md border-2 border-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-hover
}
.outline {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-transparent hover:bg-primary font-semibold text-base text-primary rounded-md outline-none border-2 border-primary hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-green-600 focus:bg-primary focus:text-white active:bg-primary-pressed
}
</style>