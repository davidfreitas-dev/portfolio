<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Icon from '@/components/Icon.vue';

const props = defineProps<{
  to: string;
  icon: string;
  text: string;
  isExpanded: boolean;
}>();

const route = useRoute();

const isActive = computed(() => route.path === props.to);

const menuItemStyle = computed(() =>
  isActive.value
    ? 'bg-primary dark:bg-primary-dark'
    : 'hover:bg-primary-accent dark:hover:bg-primary-accent-dark hover:text-primary-pressed dark:hover:text-primary-dark'
);
</script>

<template>
  <div class="menu-item flex justify-center">
    <router-link
      :to="to"
      :class="[
        'flex items-center text-decoration-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-pressed transition ease-in-out duration-200 rounded-xl w-full p-3',
        menuItemStyle
      ]"
    >
      <Icon
        :name="icon"
        :class="{ 'text-white': isActive }"
      />
      <span 
        v-if="isExpanded" 
        :class="['ml-2 font-medium', { 'text-white': isActive, }]"
      >
        {{ text }}
      </span>
    </router-link>
  </div>
</template>
