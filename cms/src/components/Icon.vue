<script setup lang="ts">
defineOptions({ 
  inheritAttrs: false 
});

defineProps<{
  name: string;
}>();
</script>

<template>
  <span class="material-icons-outlined" :class="$attrs.class">
    {{ name }}
  </span>
</template>
