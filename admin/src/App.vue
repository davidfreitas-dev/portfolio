<script setup>
import { RouterView } from 'vue-router';
import Sidebar from './components/Sidebar.vue';
</script>

<template>
  <div class="app flex">
    <Sidebar />
    <RouterView />
  </div>
</template>

<style scoped>

</style>