<script setup lang="ts">
import { ref } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import Sidebar from '@/components/Sidebar.vue';
import Toast from '@/components/Toast.vue';

const route = useRoute();

const { isAuthenticated } = storeToRefs(useAuthStore());

const sidebarWidth = ref<string>('240px');

const changeSidebarWidth = (event: string): void => {
  sidebarWidth.value = event;
};

const { toast, toastData } = useToast();
</script>

<template>
  <div class="app flex bg-background dark:bg-background-dark">
    <Sidebar v-if="isAuthenticated" @on-width-change="changeSidebarWidth" />
    <RouterView v-slot="{ Component }">
      <Transition
        appear
        mode="out-in"
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <component
          :is="Component"
          :key="route.fullPath"
          :sidebar-width="sidebarWidth"
        />
      </Transition>
    </RouterView>
    <Toast ref="toast" :toast-data="toastData" />
  </div>
</template>
