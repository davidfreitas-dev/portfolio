<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { useProfileStore } from '@/stores/profileStore';
import Icon from '@/components/Icon.vue';
import Dialog from '@/components/Dialog.vue';

defineProps<{
 sidebarWidth: string;
}>();

const emit = defineEmits<{
 (e: 'toggleSidebar'): void;
}>();

const router = useRouter();
const authStore = useAuthStore();
const profileStore = useProfileStore();

const isDropdownOpen = ref(false);
const logoutDialog = ref<InstanceType<typeof Dialog> | null>(null);

const userName = computed(() => profileStore.user?.name || 'Usuário');
const userRole = computed(() => {
  const role = profileStore.user?.role;
  return role === 'admin' ? 'Administrador' : (role || 'Usuário');
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

const openLogoutConfirm = () => {
  isDropdownOpen.value = false;
  logoutDialog.value?.openModal();
};

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};
</script>

<template>
  <header
    class="fixed top-0 right-0 h-20 backdrop-blur-xl z-40 px-6 flex items-center justify-between gap-6 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-[0_1px_8px_rgba(0,0,0,0.04)] transition-all duration-300"
    :style="{ left: sidebarWidth }"
  >
    <button
      class="p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-full transition-colors"
      @click="emit('toggleSidebar')"
    >
      <Icon name="menu" />
    </button>
 
    <div class="relative">
      <div 
        class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition-opacity"
        @click="toggleDropdown"
      >
        <div class="w-10 h-10 rounded-full bg-primary-default flex items-center justify-center text-white">
          <Icon name="person" class="text-[20px]" />
        </div>
        <div class="flex flex-col text-left">
          <span class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight">{{ userName }}</span>
          <span class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ userRole }}</span>
        </div>
        <Icon
          name="expand_more"
          class="text-gray-500 dark:text-gray-400 text-[20px] ml-1 transition-transform duration-200"
          :class="{ 'rotate-180': isDropdownOpen }"
        />
      </div>

      <!-- Dropdown Menu -->
      <div 
        v-if="isDropdownOpen"
        class="absolute right-0 mt-3 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-600 py-2 z-50"
      >
        <router-link 
          to="/profile"
          class="group flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-default transition-colors"
          @click="isDropdownOpen = false"
        >
          <Icon name="person" class="text-[18px] text-gray-500 dark:text-gray-400 group-hover:text-primary-default transition-colors" />
          Perfil
        </router-link>
        <div class="h-px bg-gray-100 dark:bg-gray-600 my-1" />
        <button 
          class="w-full group flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-danger dark:hover:text-danger-dark transition-colors text-left"
          @click="openLogoutConfirm"
        >
          <Icon name="logout" class="text-[18px] text-gray-500 dark:text-gray-400 group-hover:text-danger dark:group-hover:text-danger-dark transition-colors" />
          Sair
        </button>
      </div>
    </div>

    <Dialog
      ref="logoutDialog"
      header="Confirmar Saída"
      message="Tem certeza que deseja sair do sistema?"
      @confirm-action="handleLogout"
    />
  </header>
</template>
