<script setup lang="ts">
defineProps<{ isExpanded: boolean }>();
</script>

<template>
  <div class="logo">
    <div :class="['flex items-center', { 'justify-center': !isExpanded }]">
      <h3
        v-if="isExpanded"
        class="text-font dark:text-font-dark text-2xl font-extrabold ml-1"
      >
        Dave<span class="text-primary ml-0.5">Dev</span>
      </h3>
      <h3
        v-else
        class="text-font dark:text-font-dark text-2xl font-extrabold ml-1"
      >
        D<span class="text-primary ml-0.5">D</span>
      </h3>
    </div>
  </div>
</template>
