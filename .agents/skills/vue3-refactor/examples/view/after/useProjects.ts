import { ref, computed, watch } from 'vue';
import { debounce } from 'vue-debounce';
import { useProjectsStore } from '@/stores/projectsStore';
import type { Option } from '@/types';

export function useProjects(defaultLimit = 10) {
  const projectsStore = useProjectsStore();

  const page = ref(1);
  const itemsPerPage = defaultLimit;
  const search = ref('');
  const selectedStatus = ref<Option | null>(null);

  const normalizedSearch = computed(() => search.value.trim().toLowerCase());

  const loadProjects = async (): Promise<void> => {
    await projectsStore.fetchProjects(
      page.value,
      itemsPerPage,
      normalizedSearch.value,
      selectedStatus.value?.value
    );
  };

  const debouncedLoad = debounce(loadProjects, '400ms');

  watch(search, () => {
    page.value = 1;
    debouncedLoad();
  });

  watch(selectedStatus, () => {
    page.value = 1;
    loadProjects();
  });

  watch(page, () => {
    loadProjects();
  });

  return {
    page,
    itemsPerPage,
    search,
    selectedStatus,
    loadProjects,
  };
}
