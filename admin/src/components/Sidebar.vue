<script setup>
import { ref, watchEffect } from 'vue';
import Logo from './Logo.vue';
import MenuItem from './MenuItem.vue';

const sidebarWidth = ref('w-[64px]');
const isExpanded = ref(localStorage.getItem('isExpanded') === 'true');

const EXPANDED_WIDTH = '250px';
const COLLAPSED_WIDTH = '64px';

const emit = defineEmits(['onWidthChange']);

watchEffect(() => {
  sidebarWidth.value = isExpanded.value ? 'w-[250px]' : 'w-[64px]';
  emit('onWidthChange', isExpanded.value ? EXPANDED_WIDTH : COLLAPSED_WIDTH);
});

const toggleSidebar = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem('isExpanded', isExpanded.value);
};
</script>

<template>
  <aside :class="['flex flex-col bg-background text-secondary overflow-hidden min-h-screen p-4 transition-all ease-in-out duration-200', sidebarWidth]">
    <Logo :is-expanded="isExpanded" />

    <div :class="['menu-toggle-wrap flex mb-4 select-none transition-all duration-200 relative', { 'justify-end': isExpanded, 'top-[-3rem]': isExpanded, 'top-0': !isExpanded }]">
      <button class="menu-toggle transition-all duration-200 rounded-xl focus:outline-none" @click="toggleSidebar">
        <span class="material-icons text-font p-1 text-2xl transition-all duration-200 hover:text-primary" :style="{ transform: isExpanded ? 'rotate(-180deg)' : 'none' }">
          keyboard_double_arrow_right
        </span>
      </button>
    </div>

    <div class="menu space-y-4 -mx-4">
      <MenuItem
        to="/"
        icon="home"
        text="Home"
        :is-expanded="isExpanded"
      />
      <MenuItem
        to="/experiences"
        icon="hub"
        text="Experiencias"
        :is-expanded="isExpanded"
      />
      <MenuItem
        to="/projects"
        icon="handyman"
        text="Projetos"
        :is-expanded="isExpanded"
      />
      <MenuItem
        to="/techs"
        icon="code"
        text="Tecnologias"
        :is-expanded="isExpanded"
      />
    </div>

    <div class="flex-1" />

    <div class="menu -mx-4 mb-4">
      <MenuItem
        to="/settings"
        icon="settings"
        text="Configurações"
        :is-expanded="isExpanded"
      />
    </div>
  </aside>
</template>
