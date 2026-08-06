<script setup lang="ts">
import Logo from '@/components/Logo.vue';
import MenuItem from '@/components/MenuItem.vue';
import ThemeSwitcher from '@/components/ThemeSwitcher.vue';

interface MenuItemData {
 to: string;
 icon: string;
 text: string;
 group?: string;
}

defineProps<{
 isExpanded: boolean;
}>();

const menuItems: MenuItemData[] = [
  { to: '/', icon: 'dashboard', text: 'Overview' },
  { to: '/experiences', icon: 'hub', text: 'Experiências', group: 'Portfólio' },
  { to: '/technologies', icon: 'code', text: 'Tecnologias', group: 'Portfólio' },
  { to: '/projects', icon: 'handyman', text: 'Projetos', group: 'Portfólio' },
  { to: '/design-system', icon: 'settings', text: 'Design System', group: 'Sistema' }
];
</script>

<template>
  <aside
    :class="[
      'fixed left-0 top-0 h-full bg-white dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700 z-50 flex flex-col overflow-x-hidden transition-all duration-300',
      isExpanded ? 'w-[288px]' : 'w-[80px]'
    ]"
  >
    <div :class="['py-7 flex items-center gap-2', isExpanded ? 'px-6' : 'px-0 justify-center']">
      <Logo :is-expanded="isExpanded" />
    </div>

    <nav class="flex-1 px-4 mt-4 space-y-1 overflow-y-auto overflow-x-hidden">
      <template v-for="(item, index) in menuItems" :key="item.to">
        <div
          v-if="item.group && item.group !== menuItems[index - 1]?.group"
          :class="['mt-6 flex items-center', isExpanded ? 'px-4 py-4' : 'justify-center py-5']"
        >
          <span v-if="isExpanded" class="font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider text-[11px] truncate">
            {{ item.group }}
          </span>
          <div v-else class="h-[1px] w-8 bg-gray-200 dark:bg-gray-800 rounded-full" />
        </div>
        <MenuItem
          :to="item.to"
          :icon="item.icon"
          :text="item.text"
          :is-expanded="isExpanded"
        />
      </template>
    </nav>

    <div class="mt-auto pt-4">
      <ThemeSwitcher :is-expanded="isExpanded" />
    </div>
  </aside>
</template>
