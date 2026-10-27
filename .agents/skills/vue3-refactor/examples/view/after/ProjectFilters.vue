<script setup lang="ts">
import InputSearch from '@/components/InputSearch.vue';
import Select from '@/components/Select.vue';
import type { Option } from '@/types';

const search = defineModel<string>('search', { default: '' });
const status = defineModel<Option | null>('status', { default: null });

const statusOptions: Option[] = [
  { label: 'Todos', value: 'all' },
  { label: 'Ativo', value: 1 },
  { label: 'Inativo', value: 0 },
];
</script>

<template>
  <div class="flex flex-wrap gap-4 items-center mb-6">
    <div class="w-full sm:w-72">
      <InputSearch v-model="search" placeholder="Buscar projetos..." />
    </div>
    <div class="w-full sm:w-48">
      <Select v-model="status" :options="statusOptions" label="Filtrar por Status" />
    </div>
  </div>
</template>
