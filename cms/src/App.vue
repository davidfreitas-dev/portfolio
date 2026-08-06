<script setup lang="ts">
import { ref, computed } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import Sidebar from '@/components/Sidebar.vue';
import Topbar from '@/components/Topbar.vue';
import Toast from '@/components/Toast.vue';

const route = useRoute();
const { isAuthenticated } = storeToRefs(useAuthStore());
const { toast, toastData } = useToast();

const isExpanded = ref<boolean>(localStorage.getItem('isExpanded') !== 'false');

const toggleSidebar = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem('isExpanded', String(isExpanded.value));
};

const sidebarWidth = computed(() => isExpanded.value ? '288px' : '80px');
</script>

<template>
  <div class="app min-h-screen font-body-2 text-on-surface bg-surface">
    <Sidebar v-if="isAuthenticated" :is-expanded="isExpanded" />
    
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900" :class="['transition-all duration-300', isAuthenticated ? (isExpanded ? 'pl-[288px]' : 'pl-[80px]') : '']">
      <Topbar
        v-if="isAuthenticated"
        :sidebar-width="sidebarWidth"
        @toggle-sidebar="toggleSidebar"
      />
      
      <main class="relative min-h-screen" :class="{ 'pt-20 pb-20': isAuthenticated }">
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
      </main>
    </div>
    <Toast ref="toast" :toast-data="toastData" />
  </div>
</template>
