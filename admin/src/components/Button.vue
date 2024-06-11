<script setup>
import Loader from './Loader.vue';

const props = defineProps({
  isLoading: Boolean,
  text: { 
    type: String, 
    default: 'Text Button' 
  },
  size: { 
    type: String, 
    default: '' 
  },
  color: { 
    type: String, 
    default: 'main' 
  },
});
</script>

<template>
  <button
    type="submit"
    :class="{ 
      'main': color === 'main',
      'secondary': color === 'secondary',
      'outline': color === 'outline',
      'w-full': size === 'full',
    }"
  >
    <Loader v-if="isLoading" />
    
    <span v-else>
      {{ text }}
    </span>
  </button>
</template>

<style scoped>
.main {
  @apply py-3 px-4 bg-primary rounded-md font-semibold text-white text-base transition-colors hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-green-600 focus:bg-primary active:bg-primary-pressed
}
.secondary {
  @apply py-3 px-4 bg-white font-semibold text-gray-700 text-base rounded-md border-2 border-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-hover
}
.outline {
  @apply py-3 px-4 bg-transparent hover:bg-primary font-semibold text-base text-primary rounded-md outline-none border-2 border-primary hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-green-600 focus:bg-primary focus:text-white active:bg-primary-pressed
}
</style>