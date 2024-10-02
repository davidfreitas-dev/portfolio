<script setup>
import { computed } from 'vue';

const props = defineProps({
  color: { 
    type: String, 
    default: 'white' 
  }
});

const bgColor = computed(() => {
  return props.color === 'primary' ? 'bg-primary' : 'bg-white';
});
</script>

<template>
  <div class="dots">
    <span :class="['dot-1', bgColor]" />
    <span :class="['dot-2', bgColor]" />
    <span :class="['dot-3', bgColor]" />
  </div>
</template>

<style scoped>
.dots {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 6.5rem;
  height: 1.5rem;
}
.dots > span {
  width: 10px;
  height: 10px;
  margin: 0 3px;
  border-radius: 100%;
  display: inline-block;
  animation: sk-bouncedelay 1.3s infinite ease-in-out both;
}
.dots .dot-1 {
  animation-delay: -0.32s;
}
.dots .dot-2 {
  animation-delay: -0.16s;
}

@keyframes sk-bouncedelay {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1.0);
  }
}
</style>