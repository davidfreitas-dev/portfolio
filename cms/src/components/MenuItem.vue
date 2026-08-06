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

const linkClasses = computed(() =>
  isActive.value
    ? 'bg-primary-default text-white font-semibold shadow-sm'
    : 'text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100'
);

const iconClasses = computed(() =>
  isActive.value
    ? 'text-white'
    : 'text-gray-400 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300'
);
</script>

<template>
  <router-link
    :to="to"
    :class="[
      'flex items-center rounded-lg transition-all group py-3',
      isExpanded ? 'px-4 justify-start' : 'px-0 justify-center w-12 mx-auto',
      linkClasses
    ]"
    :title="!isExpanded ? text : ''"
  >
    <Icon
      :name="icon"
      :class="['transition-colors', isExpanded ? 'mr-3' : '', iconClasses]"
    />
    <span 
      v-if="isExpanded"
      class="font-button-md text-button-md whitespace-nowrap"
    >
      {{ text }}
    </span>
  </router-link>
</template>
