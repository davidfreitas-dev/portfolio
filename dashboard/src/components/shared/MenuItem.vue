<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  to: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  text: {
    type: String,
    default: ''
  },
  isExpanded: {
    type: Boolean,
    default: false
  }
});

const route = useRoute();

const isActive = computed(() => route.path === props.to);
</script>

<template>
  <div class="button flex justify-center">
    <router-link :to="to" :class="['flex items-center text-decoration-none focus:outline-none focus:ring-2 focus:ring-green-600 transition ease-in-out duration-200 rounded-xl w-[80%] px-5 py-2.5', { 'bg-primary': isActive, 'hover:bg-accent hover:text-secondary': !isActive }]">
      <span :class="['material-icons text-2xl', { 'text-white': isActive, 'mr-4': isExpanded, '-ml-1.5': !isExpanded }]">
        {{ icon }}
      </span>
      <span :class="['text', { 'text-white': isActive, 'opacity-100': isExpanded, 'opacity-0': !isExpanded }]">
        {{ text }}
      </span>
    </router-link>
  </div>
</template>
