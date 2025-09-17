<script setup lang="ts">
import { ref, watchEffect } from 'vue';
import Logo from '@/components/Logo.vue';
import MenuItem from '@/components/MenuItem.vue';

interface MenuItemData {
  to: string;
  icon: string;
  text: string;
}

const menuItems: MenuItemData[] = [
  { to: '/', icon: 'home', text: 'Início' },
  { to: '/experiences', icon: 'hub', text: 'Experiências' },
  { to: '/settings', icon: 'settings', text: 'Configurações' }
];

const sidebarWidth = ref<string>('w-[65px]');
const isExpanded = ref<boolean>(localStorage.getItem('isExpanded') === 'true');

const EXPANDED_WIDTH = '240px';
const COLLAPSED_WIDTH = '65px';

const emit = defineEmits<{
  (e: 'onWidthChange', width: string): void;
}>();

watchEffect(() => {
  sidebarWidth.value = isExpanded.value ? 'w-[240px]' : 'w-[65px]';
  emit('onWidthChange', isExpanded.value ? EXPANDED_WIDTH : COLLAPSED_WIDTH);
});

const toggleSidebar = (): void => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem('isExpanded', String(isExpanded.value));
};
</script>

<template>
  <aside
    :class="[
      'flex flex-col text-secondary dark:text-secondary-dark border-r border-neutral dark:border-neutral-dark overflow-hidden min-h-screen p-6 transition-all ease-in-out duration-200',
      { 'px-2': !isExpanded },
      sidebarWidth
    ]"
  >
    <div class="header flex flex-col gap-3 h-24" :class="{ 'flex-row justify-between items-start': isExpanded }">
      <Logo :is-expanded="isExpanded" />

      <button class="menu-toggle transition-all duration-200 focus:outline-none cursor-pointer" @click="toggleSidebar">
        <span 
          class="material-icons transition-all duration-200 p-1" 
          :style="{ transform: !isExpanded ? 'rotate(-180deg)' : 'none' }"
        >
          chevron_left
        </span>
      </button>
    </div>

    <div class="menu space-y-4">
      <MenuItem
        v-for="item in menuItems.slice(0, -1)"
        :key="item.to"
        :to="item.to"
        :icon="item.icon"
        :text="item.text"
        :is-expanded="isExpanded"
      />
    </div>

    <div class="flex-1" />

    <div class="menu">
      <MenuItem
        :to="menuItems[menuItems.length - 1]?.to"
        :icon="menuItems[menuItems.length - 1]?.icon"
        :text="menuItems[menuItems.length - 1]?.text"
        :is-expanded="isExpanded"
      />
    </div>
  </aside>
</template>
