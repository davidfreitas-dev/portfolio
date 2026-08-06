import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import { projectService } from '@/services/projectService';
import type { Project } from '@/types';

export interface ProjectPayload {
  id?: number;
  title: string;
  description: string;
  link?: string;
  image?: File | string;
  is_active?: number;
  technologies: { id: number; name: string }[];
}

export const useProjectsStore = defineStore('projects', () => {
  const projects: Ref<Project[]> = ref([]);
  const selectedProject: Ref<Project | null> = ref(null);
  const totalItems: Ref<number> = ref(0);
  const totalPages: Ref<number> = ref(1);

  const fetchProjects = async (page = 1, limit = 10, search = '', status?: string | number) => {
    const data = await projectService.fetchProjects(page, limit, search, status);
    projects.value = data.projects || [];
    totalItems.value = data.total ?? 0;
    totalPages.value = data.pages ?? 1;
  };

  const fetchProjectById = async (id: number) => {
    const data = await projectService.fetchProjectById(id);
    selectedProject.value = data;
  };

  const saveProject = async (payload: ProjectPayload): Promise<void> => {
    const formData = new FormData();

    if (payload.id) formData.append('id', String(payload.id));
    formData.append('title', payload.title);
    formData.append('description', payload.description);
    if (payload.link) formData.append('link', payload.link);
    if (payload.image instanceof File) formData.append('image', payload.image);
    if (payload.is_active !== undefined) formData.append('is_active', String(payload.is_active));
    if (payload.technologies) {
      formData.append('technologies', payload.technologies.map(t => t.id).join(','));
    }

    await projectService.saveProject(formData);
  };

  const deleteProject = async (id: number): Promise<void> => {
    await projectService.deleteProject(id);
  };

  return {
    projects,
    selectedProject,
    totalItems,
    totalPages,
    fetchProjects,
    fetchProjectById,
    saveProject,
    deleteProject
  };
});
