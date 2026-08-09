<script setup lang="ts">
import { reactive, computed, watch } from 'vue';

const props = defineProps({
  totalItems: { type: Number, required: true },
  itemsPerPage: { type: Number, default: 5 },
  modelValue: { type: Number, default: 1 }, // página atual
});

const emit = defineEmits(['update:modelValue']);

const state = reactive({
  currentPage: props.modelValue,
  maxVisiblePages: 5,
});

watch(() => state.currentPage, (newPage) => emit('update:modelValue', newPage));

const totalPages = computed(() => Math.ceil(props.totalItems / props.itemsPerPage));

const itemRange = computed(() => {
  const start = (state.currentPage - 1) * props.itemsPerPage + 1;
  const end = Math.min(state.currentPage * props.itemsPerPage, props.totalItems);
  return `${start} a ${end}`;
});

const visiblePages = computed(() => {
  let start = Math.max(1, state.currentPage - Math.floor(state.maxVisiblePages / 2));
  let end = start + state.maxVisiblePages - 1;

  if (end > totalPages.value) {
    end = totalPages.value;
    start = Math.max(1, end - state.maxVisiblePages + 1);
  }

  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const goToFirst = () => { state.currentPage = 1; };
const goToLast = () => { state.currentPage = totalPages.value; };
const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) state.currentPage = page;
};
</script>


<template>
  <div v-if="totalItems" class="flex flex-col md:flex-row items-center justify-between h-16 my-5 md:m-0">
    <span class="text-sm text-gray-400 dark:text-gray-300">
      Exibindo {{ itemRange }} de {{ totalItems }} itens
    </span>

    <nav>
      <ul class="flex items-center gap-1 md:gap-2 text-sm font-medium">
        <li>
          <a
            class="flex items-center justify-center h-9 px-3 transition-all duration-200 rounded-lg select-none"
            :class="[
              state.currentPage === 1 
                ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' 
                : 'text-gray-600 dark:text-gray-400 hover:text-primary-default dark:hover:text-primary-default hover:bg-primary-bg dark:hover:bg-primary-dark/20 cursor-pointer'
            ]"
            @click="state.currentPage !== 1 && goToFirst()"
          >
            Primeira
          </a>
        </li> 
        <li v-for="page in visiblePages" :key="page">
          <a
            class="flex items-center justify-center h-9 w-9 transition-all duration-200 rounded-lg select-none cursor-pointer"
            :class="[
              page === state.currentPage 
                ? 'bg-primary-default text-white shadow-md' 
                : 'text-gray-600 dark:text-gray-400 hover:text-primary-default dark:hover:text-primary-default hover:bg-primary-bg dark:hover:bg-primary-dark/20'
            ]"
            @click="goToPage(page)"
          >
            {{ page }}
          </a>
        </li>
        <li>
          <a
            class="flex items-center justify-center h-9 px-3 transition-all duration-200 rounded-lg select-none"
            :class="[
              state.currentPage === totalPages 
                ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' 
                : 'text-gray-600 dark:text-gray-400 hover:text-primary-default dark:hover:text-primary-default hover:bg-primary-bg dark:hover:bg-primary-dark/20 cursor-pointer'
            ]"
            @click="state.currentPage !== totalPages && goToLast()"
          >
            Última
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>
