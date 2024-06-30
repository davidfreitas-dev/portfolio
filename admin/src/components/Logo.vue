<script setup>
const props = defineProps({
  isExpanded: {
    type: Boolean,
    default: false
  }
});
</script>

<template>
  <div class="logo mb-4">
    <h3 v-if="isExpanded" class="text-font text-2xl font-extrabold ml-2">
      Dave<small class="text-primary text-base ml-0.5">dev</small>
    </h3>
    <h3 v-else class="text-font text-2xl font-extrabold ml-1">
      D<small class="text-primary text-base">d</small>
    </h3>
  </div>
</template>
