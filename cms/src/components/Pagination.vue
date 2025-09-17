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
    <span class="text-sm text-gray-400">
      Exibindo {{ itemRange }} de {{ totalItems }} itens
    </span>

    <nav>
      <ul class="flex items-center gap-1.5 md:gap-3 text-sm text-gray-400">
        <li>
          <a
            class="bg-white dark:bg-background-dark hover:bg-primary-hover hover:text-white border border-neutral dark:border-neutral-dark hover:border-primary transition-colors rounded-md md:rounded-lg cursor-pointer select-none py-1 px-2.5 md:py-2.5 md:px-4"
            :class="{ 'cursor-not-allowed': state.currentPage === 1 }"
            @click="goToFirst"
          >
            Primeira
          </a>
        </li>        
        <li v-for="page in visiblePages" :key="page">
          <a
            class="hover:bg-primary-hover hover:text-white border border-neutral dark:border-neutral-dark hover:border-primary transition-colors rounded-md md:rounded-lg cursor-pointer select-none py-1 px-2.5 md:py-2.5 md:px-4"
            :class="{ 'bg-primary-hover text-white border-primary': page === state.currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </a>
        </li>
        <li>
          <a
            class="bg-white dark:bg-background-dark hover:bg-primary-hover hover:text-white border border-neutral dark:border-neutral-dark hover:border-primary transition-colors rounded-md md:rounded-lg cursor-pointer select-none py-1 px-2.5 md:py-2.5 md:px-4"
            :class="{ 'cursor-not-allowed': state.currentPage === totalPages }"
            @click="goToLast"
          >
            Última
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>
