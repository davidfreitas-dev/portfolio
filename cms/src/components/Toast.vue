<script setup lang="ts">
import { ref, computed } from 'vue';
import type { ToastData } from '@/types';
import Icon from '@/components/Icon.vue';
import 'animate.css';

const { toastData } = defineProps<{
 toastData: ToastData;
}>();

const isShowing = ref(false);
const animationClass = ref('');

const showToast = () => {
  animationClass.value = 'animate__bounceInRight';
  isShowing.value = true;

  setTimeout(() => {
    animationClass.value = 'animate__bounceOutRight';
  }, 3000);
};

const handleAnimationEnd = () => {
  if (animationClass.value === 'animate__bounceOutRight') {
    isShowing.value = false;
    animationClass.value = '';
  }
};

const toastIcon = computed(() => {
  switch (toastData.type) {
  case 'success':
    return 'check';
  case 'error':
    return 'close';
  case 'warning':
    return 'warning';
  case 'info':
  default:
    return 'info';
  }
});

defineExpose({ showToast });
</script>

<template>
  <div
    v-if="isShowing"
    id="toast"
    role="alert"
    :class="[
      'fixed z-50 top-5 right-6 flex items-center p-4 mb-4 w-full max-w-xs rounded-xl shadow-lg border backdrop-blur-sm animate__animated',
      animationClass,
      'bg-white/95 dark:bg-gray-800/95 border-gray-100 dark:border-gray-700 text-gray-800 dark:text-gray-100'
    ]"
    @animationend="handleAnimationEnd"
  >
    <div
      class="inline-flex flex-shrink-0 justify-center items-center w-10 h-10 rounded-lg shadow-sm"
      :class="{
        'bg-success-accent text-success dark:bg-success-accent-dark dark:text-success-hover': toastData.type === 'success',
        'bg-danger-accent text-danger dark:bg-danger-accent-dark dark:text-danger-hover': toastData.type === 'error',
        'bg-warning-accent text-warning dark:bg-warning-accent-dark dark:text-warning-hover': toastData.type === 'warning',
        'bg-primary-light text-primary-dark dark:bg-primary-dark dark:text-primary-light': toastData.type === 'info'
      }"
    >
      <Icon :name="toastIcon" class="w-5 h-5" />
      <span class="sr-only">Icon</span>
    </div>

    <div class="ml-3 text-sm font-medium">
      {{ toastData.message }}
    </div>
  </div>
</template>
