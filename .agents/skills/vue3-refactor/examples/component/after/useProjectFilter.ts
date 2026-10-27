import { computed, type Ref } from 'vue';
import type { Project } from '@/types';

export function useProjectFilter(
  getProjects: () => Project[],
  search: Ref<string>
) {
  const filteredProjects = computed(() => {
    const term = search.value.trim().toLowerCase();
    const list = getProjects();
    if (!term) return list;

    return list.filter((project) =>
      project.title.toLowerCase().includes(term) ||
      (project.summary && project.summary.toLowerCase().includes(term))
    );
  });

  return { filteredProjects };
}
