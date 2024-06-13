<script setup>
import { ref, watchEffect } from 'vue';
import Logo from './Logo.vue';
import MenuItem from './MenuItem.vue';

const isExpanded = ref(localStorage.getItem('isExpanded') === 'true');
const menuWidth = ref('w-[calc(2rem+32px)]');

const toggleMenu = () => {
  isExpanded.value = !isExpanded.value;
  localStorage.setItem('isExpanded', isExpanded.value);
};

watchEffect(() => {
  menuWidth.value = isExpanded.value ? 'w-sidebar-width' : 'w-[calc(2rem+32px)]';
});
</script>

<template>
  <aside class="flex flex-col bg-background text-secondary overflow-hidden min-h-screen p-4 transition-all ease-in-out duration-200" :class="menuWidth">
    <Logo :is-expanded="isExpanded" />

    <div :class="['menu-toggle-wrap flex mb-4 select-none transition-all duration-200 relative', { 'justify-end': isExpanded, 'top-[-3rem]': isExpanded, 'top-0': !isExpanded }]">
      <button class="menu-toggle transition-all duration-200 rounded-xl focus:outline-none" @click="toggleMenu">
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
