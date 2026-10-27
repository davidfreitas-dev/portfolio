<script setup lang="ts">
import { ref } from 'vue';
import InputSearch from '@/components/InputSearch.vue';
import Loader from '@/components/Loader.vue';
import Badge from '@/components/Badge.vue';
import type { Project } from '@/types';
import { useProjectFilter } from './useProjectFilter';

const { projects = [], loading = false } = defineProps<{
  projects?: Project[];
  loading?: boolean;
}>();

const emit = defineEmits<{
  (e: 'select', project: Project): void;
}>();

const search = ref('');
const { filteredProjects } = useProjectFilter(() => projects, search);
</script>

<template>
  <div class="space-y-4">
    <InputSearch v-model="search" placeholder="Buscar projeto..." />

    <div v-if="loading" class="flex justify-center p-6">
      <Loader color="primary" />
    </div>

    <ul v-else-if="filteredProjects.length" class="divide-y divide-gray-200 dark:divide-gray-700">
      <li
        v-for="project in filteredProjects"
        :key="project.id"
        class="py-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 px-3 rounded-lg transition-colors"
        @click="emit('select', project)"
      >
        <div>
          <span class="font-medium text-gray-800 dark:text-gray-100 block">
            {{ project.title }}
          </span>
          <span v-if="project.summary" class="text-xs text-gray-500 dark:text-gray-400">
            {{ project.summary }}
          </span>
        </div>
        <Badge
          :label="project.is_active ? 'Ativo' : 'Inativo'"
          :color="project.is_active ? 'success' : 'danger'"
        />
      </li>
    </ul>

    <p v-else class="text-sm text-gray-500 dark:text-gray-400 py-4 text-center">
      Nenhum projeto encontrado.
    </p>
  </div>
</template>
