<script setup>
import { reactive, computed, watch } from 'vue';

const props = defineProps({
  totalPages: {
    type: Number,
    default: 10
  },
  totalItems: {
    type: Number,
    default: 0
  }
});

const state = reactive({
  currentPage: 1,
  maxVisiblePages: 5,
});

const emit = defineEmits(['onPageChange']);

watch(
  () => state.currentPage,
  (newPage) => {
    emit('onPageChange', newPage);
  }
);

const visiblePages = computed(() => {
  const startPage = Math.max(1, state.currentPage - Math.floor(state.maxVisiblePages / 2));
  const endPage = Math.min(props.totalPages, startPage + state.maxVisiblePages - 1);
  const pages = [];

  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }

  return pages;
});

const previousPage = () => {
  if (state.currentPage > 1) {
    state.currentPage--;
  }
};

const nextPage = () => {
  if (state.currentPage < props.totalPages) {
    state.currentPage++;
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= props.totalPages) {
    state.currentPage = page;
  }
};

const resetPagination = () => {
  state.currentPage = 1;
};

defineExpose({
  resetPagination
});
</script>

<template>
  <div class="flex flex-col md:flex-row items-center justify-between h-16 mt-5">
    <span class="text-sm text-gray-400">
      Exibindo 1 a 3 de {{ totalItems }} itens
    </span>

    <nav>
      <ul class="flex items-center gap-1.5 md:gap-3 text-sm text-gray-400">
        <li>
          <a
            class="bg-white hover:bg-primary-hover hover:text-white border hover:border-primary transition-colors rounded-md md:rounded-lg cursor-pointer select-none py-1 px-2.5 md:py-2.5 md:px-4"
            :disabled="state.currentPage === 1"
            @click="previousPage"
          >
            Anterior
          </a>
        </li>
        <li
          v-for="page in visiblePages"
          :key="page"
        >
          <a
            class="hover:bg-primary-hover hover:text-white border hover:border-primary transition-colors rounded-md md:rounded-lg cursor-pointer select-none py-1 px-2.5 md:py-2.5 md:px-4"
            :class="{ 'bg-primary-hover text-white border-primary': page == state.currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </a>
        </li>
        <li>
          <a
            class="bg-white hover:bg-primary-hover hover:text-white border hover:border-primary transition-colors rounded-md md:rounded-lg cursor-pointer select-none py-1 px-2.5 md:py-2.5 md:px-4"
            :disabled="state.currentPage === props.totalPages"
            @click="nextPage"
          >
            Próxima
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>