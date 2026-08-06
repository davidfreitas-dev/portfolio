<script setup lang="ts">
defineProps<{ isExpanded: boolean }>();
</script>

<template>
  <div class="logo w-full flex items-center justify-center">
    <h3
      v-if="isExpanded"
      class="text-gray-700 dark:text-gray-100 text-[26px] font-extrabold tracking-tight leading-none text-center"
    >
      Dave<span class="text-primary-default ml-0.5">Dev</span>
    </h3>
    <h3
      v-else
      class="text-gray-700 dark:text-gray-100 text-[26px] font-extrabold tracking-tight leading-none text-center"
    >
      D<span class="text-primary-default ml-0.5">D</span>
    </h3>
  </div>
</template>
