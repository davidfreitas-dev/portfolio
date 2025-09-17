<script setup lang="ts">
import { computed } from 'vue';

type DotColor = 'white' | 'primary';

const { color = 'white' } = defineProps<{
  color?: DotColor;
}>();

const bgColor = computed(() => {
  return color === 'primary' ? 'bg-primary dark:bg-primary-dark' : 'bg-white';
});
</script>

<template>
  <div class="flex justify-center items-center w-[6.5rem] h-6">
    <span :class="['w-[10px] h-[10px] mx-[3px] rounded-full inline-block animate-dot-delay-1', bgColor]" />
    <span :class="['w-[10px] h-[10px] mx-[3px] rounded-full inline-block animate-dot-delay-2', bgColor]" />
    <span :class="['w-[10px] h-[10px] mx-[3px] rounded-full inline-block animate-dot-delay-3', bgColor]" />
  </div>
</template>

<style scoped>
@keyframes sk-bouncedelay {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1.0);
  }
}
.animate-dot-delay-1 {
  animation: sk-bouncedelay 1.3s infinite ease-in-out both;
  animation-delay: -0.32s;
}
.animate-dot-delay-2 {
  animation: sk-bouncedelay 1.3s infinite ease-in-out both;
  animation-delay: -0.16s;
}
.animate-dot-delay-3 {
  animation: sk-bouncedelay 1.3s infinite ease-in-out both;
}
</style>
