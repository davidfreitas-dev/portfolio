<script setup>
import { ref, computed } from 'vue';
import { RouterView } from 'vue-router';
import { useSessionStore } from './stores/session';
import Sidebar from './components/shared/Sidebar.vue';

const storeSession = useSessionStore();

const invalidSession = computed(() => {
  return !storeSession.session || !storeSession.session.token;
});

const sidebarWidth = ref('300px');

const changeSidebarWidth = (event) => {
  sidebarWidth.value = event;
};
</script>

<template>
  <div class="app flex">
    <Sidebar v-if="!invalidSession" @on-width-change="changeSidebarWidth" />
    <RouterView v-slot="{ Component }">
      <component :is="Component" :sidebar-width="sidebarWidth" />
    </RouterView>
  </div>
</template>
