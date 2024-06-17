<script setup>
import { ref } from 'vue';
import { RouterView } from 'vue-router';
import Sidebar from './components/Sidebar.vue';

const sidebarWidth = ref('300px');

const changeSidebarWidth = (event) => {
  sidebarWidth.value = event;
};
</script>

<template>
  <div class="app flex">
    <Sidebar @on-width-change="changeSidebarWidth" />
    <RouterView v-slot="{ Component }">
      <component :is="Component" :sidebar-width="sidebarWidth" />
    </RouterView>
  </div>
</template>
