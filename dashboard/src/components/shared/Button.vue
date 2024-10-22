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
  <button 
    type="submit" 
    :class="{ 
      'primary': color === 'primary', 
      'secondary': color === 'secondary', 
      'outline': color === 'outline', 
      'danger': color === 'danger',
      'small-button': size === 'small',
      'w-full text-center': size === 'full' 
    }"
  >
    <Loader v-if="isLoading" />
    <slot v-else />
  </button>
</template>

<style scoped>
.primary {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-primary font-semibold text-white text-base rounded-md cursor-pointer transition-colors hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-green-600 focus:bg-primary active:bg-primary-pressed disabled:bg-gray-300 disabled:cursor-not-allowed
}
.secondary {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-white font-semibold text-gray-700 text-base rounded-md cursor-pointer border-2 border-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300 disabled:bg-gray-300 disabled:text-white disabled:cursor-not-allowed
}
.outline {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-transparent font-semibold text-base text-primary rounded-md cursor-pointer border-2 border-primary hover:bg-primary outline-none hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-green-600 focus:bg-primary focus:text-white active:bg-primary-pressed
}
.danger {
  @apply flex items-center justify-center gap-1.5 py-3.5 px-4 bg-red-500 font-semibold text-white text-base rounded-md cursor-pointer transition-colors hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:bg-red-500 active:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed;
}
.small-button {
  @apply p-2;
}
</style>